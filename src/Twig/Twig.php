<?php

namespace Leeroy\Forms\Twig;

use Leeroy\Forms\Services\Recaptcha;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Twig extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('recaptchaTag', [$this, 'recaptchaTag']),
            new TwigFunction('recaptchaScript', [$this, 'recaptchaScript']),
        ];
    }

    public function recaptchaTag(string $version, string $site_key = ""): string
    {
        return (new Recaptcha())->recaptchaTag($version, $site_key);
    }

    public function recaptchaScript(string $form_id, string $version): string
    {
        return (new Recaptcha())->recaptchaScript($form_id, $version);
    }
}