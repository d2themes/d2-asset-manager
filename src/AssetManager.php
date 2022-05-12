<?php

namespace D2\Theme;

class AssetManager extends Component
{
    const SCRIPTS = 'scripts';
    const STYLES = 'styles';
    const HANDLE = 'handle';
    const URL = 'src';
    const DEPS = 'deps';
    const VERSION = 'version';
    const FOOTER = 'footer';
    const MEDIA = 'media';
    const ENQUEUE = 'enqueue';
    const LOCALIZE = 'localize';
    const LOCALIZED_VAR = 'l10var';
    const LOCALIZED_DATA = 'l10ndata';

    public function init()
    {
        if (array_key_exists(self::SCRIPTS, $this->config)) {
            add_action('wp_enqueue_scripts', [$this, 'process_scripts']);
        }

        if (array_key_exists(self::STYLES, $this->config)) {
            add_action('wp_enqueue_scripts', [$this, 'process_styles']);
        }
    }

    public function process_scripts()
    {
        foreach ($this->config[self::SCRIPTS] as $asset) {
            $deps = $this->get_deps($asset);
            $version = $this->get_version($asset);
            $footer = $this->get_footer($asset);
            $function = true === $asset[self::ENQUEUE] ? 'wp_enqueue_script' : 'wp_register_script';

            // Either enqueue or register the script.
            $function($asset[self::HANDLE], $asset[self::URL], $deps, $version, $footer);

            if (array_key_exists(self::LOCALIZE, $asset)) {
                $name = $asset[self::LOCALIZE][self::LOCALIZED_VAR];
                $data = $asset[self::LOCALIZE][self::LOCALIZED_DATA];
                wp_localize_script($asset[self::HANDLE], $name, $data);
            }
        }
    }

    /**
     * Enqueue or register stylesheets passed through config.
     *
     * @return void
     */
    public function process_styles()
    {
        foreach ($this->config[self::STYLES] as $asset) {
            $deps = $this->get_deps($asset);
            $version = $this->get_version($asset);
            $media = $this->get_media($asset);
            $function = true === $asset[self::ENQUEUE] ? 'wp_enqueue_style' : 'wp_register_style';

            // Either enqueue or register the stylesheet.
            $function($asset[self::HANDLE], $asset[self::URL], $deps, $version, $media);
        }
    }

    /**
     * Get asset dependencies, or fall back to empty array.
     *
     * @param array $asset
     *
     * @return array
     */
    protected function get_deps(array $asset): array
    {
        return $asset[self::DEPS] ?? [];
    }

    /**
     * Get asset version, or fall back to false.
     *
     * @param array $asset
     *
     * @return string|bool
     */
    protected function get_version(array $asset): bool|string
    {
        return $asset[self::VERSION] ?? false;
    }

    /**
     * Determine if asset should be loaded in the footer.
     *
     * @param array $asset
     *
     * @return bool
     */
    protected function get_footer(array $asset): bool
    {
        return $asset[self::FOOTER] ?? false;
    }

    /**
     * Determine media type, or fall back to 'all'.
     *
     * @param array $asset
     *
     * @return string
     */
    protected function get_media(array $asset): string
    {
        return $asset[self::MEDIA] ?? 'all';
    }
}