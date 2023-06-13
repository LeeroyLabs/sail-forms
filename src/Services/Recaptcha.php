<?php

namespace Leeroy\Forms\Services;

use Leeroy\Forms\Enum\Version;

class Recaptcha
{
    /**
     *
     * Return recaptcha module
     *
     * @param string $site_key
     * @param string $version
     *
     * @return string
     */
    public function recaptchaTag(string $version, string $site_key = ""): string
    {
        if (!$site_key) {
            $site_key = env('RECAPTCHA_SITE_KEY');
        }

        $tag = "";
        switch ($version) {
            case Version::V2Checkbox->name:
                $tag = '<div class="g-recaptcha" data-sitekey="'. $site_key .'"></div>';
                break;
            case Version::V2Invisible->name:
                $tag = '<button class="g-recaptcha" data-sitekey="'. $site_key .'" data-callback="onSubmit">Submit</button>';
                break;
            case Version::V3->name:
                $tag = '<button class="g-recaptcha" data-sitekey="'. $site_key .'" data-callback="onSubmit" data-action="submit">Submit</button>';
                break;
        }

        return $tag;
    }

    /**
     *
     * Return recaptcha script(s)
     *
     * @param string $form_id
     * @param string $version
     *
     * @return string
     */
    public function recaptchaScript(string $form_id, string $version): string
    {
        $script = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        if($version === Version::V2Invisible->name || $version === Version::V3->name) {
            $script .= '<script>function onSubmit(token) { document.getElementById("'. $form_id .'").submit(); }</script>';
        }

        return $script;
    }
}