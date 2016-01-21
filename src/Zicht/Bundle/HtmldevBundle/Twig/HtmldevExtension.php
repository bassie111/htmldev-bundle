<?php

namespace Zicht\Bundle\HtmldevBundle\Twig;

use Symfony\Component\Yaml\Yaml;
use Twig_Extension;
use Zicht\Bundle\HtmldevBundle\Service\IColorService;

/**
 * Twig extensions that make rendering a style guide easier.
 *
 * @package Zicht\Bundle\HtmldevBundle\Twig
 */
class HtmldevExtension extends Twig_Extension
{
    private $htmldevDirectory;

    /**
     * @var IColorService
     */
    private $colorService;

    public function __construct($htmldevDirectory, IColorService $colorService)
    {
        $this->htmldevDirectory = $htmldevDirectory;
        $this->colorService = $colorService;
    }

    /**
     * Gets the list of functions available in the Twig templates.
     *
     * @return array
     */
    function getFunctions() {
        return array(
            new \Twig_SimpleFunction('ui_and_html', array($this, 'ui_and_html'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_ui_and_html', array($this, 'get_ui_and_html'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_current_datetime', array($this, 'get_current_datetime')),
            new \Twig_SimpleFunction('load_data', array($this, 'loadData')),
            new \Twig_SimpleFunction('icons', array($this, 'getIcons')),
            new \Twig_SimpleFunction('color_groups', array($this->colorService, 'getColorGroups')),
            new \Twig_SimpleFunction('luminance', array($this->colorService, 'getLuminance'))
        );
    }

    /**
     * Returns the current time with, optionally, a modifier
     *
     * > get_current_datetime()
     * Datetime('now')
     *
     * > get_current_datetime('+1 day')
     * Datetime('now')->modify('+1 day')
     *
     * @param string $modify
     * @return \DateTime
     */
    public function get_current_datetime($modify = '')
    {
        // use static $now to ensure that all dates are -exactly- the same
        static $now = null;
        if (null === $now) { $now = new \DateTime('now'); }

        $datetime = clone $now;
        if (!empty($modify)) {
            $datetime->modify($modify);
        }

        return $datetime;
    }

    /**
     * @param $html
     * @deprecated Use get_ui_and_html instead
     */
    public function ui_and_html($html)
    {
        return $this->get_ui_and_html($html);
    }

    /**
     * Renders the supplied HTML both as actual HTML and a code block.
     *
     * @param $html
     * @return string
     */
    public function get_ui_and_html($html)
    {
        $resultHtml = sprintf('%s
            <pre>
                <code>%s</code>
            </pre>
        ', $html, htmlentities($html));

        return $resultHtml;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function loadData($type)
    {
        $fileName = sprintf('%s/data/%s.yml', $this->htmldevDirectory, $type);
        if (!is_file($fileName)) {
            return [];
        }

        return Yaml::parse(file_get_contents($fileName));
    }

    /**
     * Returns the names of the icons of the supplied type, without file extension.
     *
     * @param $type
     *
     * @return array
     */
    public function getIcons($type)
    {
        $imageDirectory = sprintf('%s/images/icons/%s', $this->htmldevDirectory, $type);
        if (!is_dir($imageDirectory)) {
            return [];
        }

        $files = array_diff(scandir($imageDirectory), ['..', '.']);
        $iconNames = array_map(function($item) {
            return basename($item, '.svg');
        }, $files);

        return $iconNames;
    }

    /**
     * Register a 'faker' global if faker is available
     *
     * @return array
     */
    public function getGlobals()
    {
        if (class_exists('Faker\Factory')) {
            return [
                'faker' => \Faker\Factory::create()
            ];
        }
        return [];
    }

    /**
     * The name of this twig extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'htmldev_twig_extension';
    }
}
