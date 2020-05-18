<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\HtmldevBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use Symfony\Component\Yaml\Yaml;
use Zicht\Bundle\HtmldevBundle\Service\ColorServiceInterface;
use Zicht\Bundle\HtmldevBundle\Service\SvgServiceInterface;

/**
 * Twig extension for handling images.
 */
class ImageExtension extends Twig_Extension
{
    /** @var string */
    private $htmldevDirectory;

    /** @var SvgServiceInterface */
    private $svgService;

    /**
     * Initializes a new instance of the ImageExtension class.
     *
     * @param string $htmldevDirectory
     * @param SvgServiceInterface $svgService
     */
    public function __construct($htmldevDirectory, SvgServiceInterface $svgService)
    {
        $this->htmldevDirectory = $htmldevDirectory;
        $this->svgService = $svgService;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('icon_list', [$this, 'getIcons']),
            new Twig_SimpleFunction('embed_svg', [$this->svgService, 'getSvg']),
        ];
    }

    /**
     * Returns the names of the icons of the supplied type, without file extension.
     *
     * @param string $type
     * @return array
     */
    public function getIcons($type = '')
    {
        $imageDirectory = sprintf('%s/images/icons/%s', $this->htmldevDirectory, $type);
        if (!is_dir($imageDirectory)) {
            return [];
        }

        $files = array_diff(scandir($imageDirectory), ['..', '.']);
        return array_map(
            function ($item) {
            return basename($item, '.svg');
            },
            $files
        );
    }

    /**
     * The name of this twig extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'htmldev_twig_image_extension';
    }
}
