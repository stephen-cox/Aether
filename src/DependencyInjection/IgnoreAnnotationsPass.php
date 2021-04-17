<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Ignore API Doc annotations.
 */
final class IgnoreAnnotationsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        AnnotationReader::addGlobalIgnoredName('api');
        AnnotationReader::addGlobalIgnoredName('apiDefine');
        AnnotationReader::addGlobalIgnoredName('apiDeprecated');
        AnnotationReader::addGlobalIgnoredName('apiDescription');
        AnnotationReader::addGlobalIgnoredName('apiError');
        AnnotationReader::addGlobalIgnoredName('apiErrorExample');
        AnnotationReader::addGlobalIgnoredName('apiExample');
        AnnotationReader::addGlobalIgnoredName('apiGroup');
        AnnotationReader::addGlobalIgnoredName('apiHeader');
        AnnotationReader::addGlobalIgnoredName('apiHeaderExample');
        AnnotationReader::addGlobalIgnoredName('apiIgnore');
        AnnotationReader::addGlobalIgnoredName('apiName');
        AnnotationReader::addGlobalIgnoredName('apiParam');
        AnnotationReader::addGlobalIgnoredName('apiParamExample');
        AnnotationReader::addGlobalIgnoredName('apiPermission');
        AnnotationReader::addGlobalIgnoredName('apiPrivate');
        AnnotationReader::addGlobalIgnoredName('apiSampleRequest');
        AnnotationReader::addGlobalIgnoredName('apiSuccess');
        AnnotationReader::addGlobalIgnoredName('apiSuccessExample');
        AnnotationReader::addGlobalIgnoredName('apiUse');
        AnnotationReader::addGlobalIgnoredName('apiVersion');
    }
}
