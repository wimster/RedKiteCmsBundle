<?php
/**
 * This file is part of the BusinessDropCapBundle and it is distributed
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

namespace RedKiteLabs\RedKiteCmsBundle\Core\Listener\ImagesBlock;

use RedKiteLabs\RedKiteCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\Deprecated\RedKiteDeprecatedException;

/**
 * Renders the editor to manipulate a Json item
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @deprecated since 1.1.0
 * @codeCoverageIgnore
 */
abstract class BaseImagesBlockEditorListener implements ImagesListenerInterface
{
    protected $container;
    
    public function __construct()
    {
        throw new RedKiteDeprecatedException("BaseImagesBlockEditorListener has been deprecated since RedKiteCms 1.1.0");
    }
    
    /**
     * {@inheritdoc}
     */
    public function onBlockEditorRendering(BlockEditorRenderingEvent $event)
    {
        $alBlockManager = $event->getBlockManager();
        $blockType = $alBlockManager->get()->getType();
        if ($blockType == $this->getManagedBlockType()) {
            $this->container = $event->getContainer();
            $request = $this->container->get('request');
            $template = sprintf('%sBundle:Block:%s_editor.html.twig', $blockType, strtolower($blockType));

            $editorSettingsParamName = sprintf('%s.editor_settings', strtolower($blockType));
            $editorSettings = ($this->container->hasParameter($editorSettingsParamName)) ? $this->container->getParameter($editorSettingsParamName) : array();
            $blockId = $alBlockManager->get()->getId();
            $form = $this->setUpForm($blockId, -1);
            $parameters = array(
                "alContent" => $alBlockManager,
                "language" => $request->get('languageId'),
                "page" => $request->get('pageId'),
                "editor_settings" => $editorSettings,
                "form" => $form->createView(),
                "block_manager" => $alBlockManager,
                "block_id" => $blockId,
            );

            $options = $this->configure();
            if (array_key_exists('images_editor_template', $options)) {
                $parameters['images_editor_template'] = $options['images_editor_template'];
            }

            $editor = $this->container->get('templating')->render($template, $parameters);

            $event->setEditor($editor);
        }
    }

    /**
     * Sets up the form that manages the json item
     *
     * @param int The block id
     * @param int The item id
     * @return Form
     */
    protected function setUpForm($blockId, $itemId)
    {
        $item = null;
        $block = $this->fetchBlock($blockId);
        if ($itemId != -1) {
            $content = json_decode($block->getContent(), true);

            if (!array_key_exists($itemId, $content)) {
                throw new \InvalidArgumentException('exception_item_not_exists');
            }

            $item = $content[$itemId];
            $item['id'] = $itemId;
        }

        $formName = sprintf('%s.form', strtolower($block->getType()));
        $formClass = $this->container->get($formName);

        return $this->container->get('form.factory')->create($formClass, $item);
    }

    /**
     * Retrieves the block
     *
     * @param int The block id
     * @return \RedKiteLabs\RedKiteCmsBundle\Core\Repository\Repository\BlockRepositoryInterface
     */
    protected function fetchBlock($blockId)
    {
        $factoryRepository = $this->container->get('red_kite_cms.factory_repository');
        $repository = $factoryRepository->createRepository('Block');
        $block = $repository->fromPk($blockId);

        if (null == $block) {
            throw new \InvalidArgumentException('It seems that the block to edit does not exist anymore');
        }

        return $block;
    }
}
