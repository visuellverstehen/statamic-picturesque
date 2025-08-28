<?php

namespace VV\Picturesque\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset;
use Statamic\Imaging\ImageGenerator;

class GenerateImageVariants implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Asset|string $value,
        private array $glideParameters,
        private string $generateBy = 'asset'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imageGenerator = new ImageGenerator(app(Server::class));
        switch ($this->generateBy) {
            case 'asset':
                $imageGenerator->generateByAsset($this->value, $this->glideParameters);
                break;
            case 'url':
                $imageGenerator->generateByUrl($this->value, $this->glideParameters);
                break;
            case 'path':
                $imageGenerator->generateByPath($this->value, $this->glideParameters);
                break;
        }
    }

    public function uniqid(): string
    {
        $uniqid = $this->value;
        if ($this->value instanceof Asset) {
            $uniqid = $this->value->basename();
        }
        return md5($uniqid);
    }
}
