<?php

/*
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Core\Deploy;

use RedKiteLabs\RedKiteCmsBundle\Core\PageTree\AlPageTree;
use RedKiteLabs\RedKiteCmsBundle\Core\Deploy\TwigTemplateWriter\AlTwigTemplateWriterBase;
use RedKiteLabs\RedKiteCmsBundle\Core\Deploy\TwigTemplateWriter\AlTwigTemplateWriterPages;

/**
 * AlTwigDeployer extends the base deployer class to deploy the website for production
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
class AlTwigDeployerProduction extends AlTwigDeployer
{
    /**
     * {@inheritdoc}
     */
    protected function getTemplatesFolder()
    {
        return $this->container->getParameter('red_kite_labs_theme_engine.deploy.templates_folder');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoutesPrefix()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function save(AlPageTree $pageTree, $type)
    {
        $imagesPath = array(
            'backendPath' => $this->uploadAssetsAbsolutePath,
            'prodPath' => '/' . $this->deployBundleAsset->getAbsolutePath()
        );

        $credits = $this->credits;
        switch ($type) {
            case 'Base':
                $twigTemplateWriter = new AlTwigTemplateWriterBase(
                    $pageTree,
                    $this->blockManagerFactory,
                    $this->urlManager,
                    $this->viewsRenderer,
                    $imagesPath
                );
                break;
            case 'Pages':
                $credits = false;
                $twigTemplateWriter = new AlTwigTemplateWriterPages(
                    $pageTree,
                    $this->blockManagerFactory,
                    $this->urlManager,
                    $this->deployBundle,
                    $this->deployFolder,
                    $this->viewsRenderer,
                    $imagesPath
                );
                break;
        }

        return $twigTemplateWriter
            ->setCredits($credits)
            ->generateTemplate()
            ->writeTemplate($this->viewsDir)
        ;
    }
}
