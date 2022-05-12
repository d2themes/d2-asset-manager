<?php

namespace D2\Theme;

class AssetManager extends Component
{
    const SCRIPTS = 'scripts';
    const STYLES = 'styles';

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
            $function = isset($asset[Asset::ENQUEUE]) && false === $asset[Asset::ENQUEUE] ? 'wp_register_script' : 'wp_enqueue_script';

            // Either enqueue or register the script.
            $function($asset[Asset::HANDLE], $asset[Asset::URL], $deps, $version, $footer);

            if (array_key_exists(Asset::LOCALIZE, $asset)) {
                $name = $asset[Asset::LOCALIZE][Asset::LOCALIZED_VAR];
                $data = $asset[Asset::LOCALIZE][Asset::LOCALIZED_DATA];
                wp_localize_script($asset[Asset::HANDLE], $name, $data);
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
            $function = isset($asset[Asset::ENQUEUE]) && false === $asset[Asset::ENQUEUE] ? 'wp_register_style' : 'wp_enqueue_style';

            // Either enqueue or register the stylesheet.
            $function($asset[Asset::HANDLE], $asset[Asset::URL], $deps, $version, $media);
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
        return $asset[Asset::DEPS] ?? [];
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
        return $asset[Asset::VERSION] ?? false;
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
        return $asset[Asset::FOOTER] ?? false;
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
        return $asset[Asset::MEDIA] ?? 'all';
    }
}