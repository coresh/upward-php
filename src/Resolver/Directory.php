<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Upward\Resolver;

use Magento\Upward\Definition;
use Zend\Http\Header\ContentType;
use Zend\Http\Response;
use Zend\Http\Response\Stream;

class Directory extends AbstractResolver
{
    /**
     * {@inheritdoc}
     */
    public function getIndicator(): string
    {
        return 'directory';
    }

    public function isValid(Definition $definition): bool
    {
        $directory  = $this->getIterator()->get('directory', $definition);
        $upwardRoot = $definition->getBasepath();

        $root = realpath($upwardRoot . \DIRECTORY_SEPARATOR . $directory);

        if (!$root || !is_dir($root)) {
            return false;
        }

        return parent::isValid($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($definition)
    {
        $directory = $definition;

        if ($definition instanceof Definition) {
            $directory = $this->getIterator()->get('directory', $definition);
        }

        $response   = new Stream();
        $upwardRoot = $this->getIterator()->getRootDefinition()->getBasepath();
        $root       = realpath($upwardRoot . \DIRECTORY_SEPARATOR . $directory);
        $filename   = $this->getIterator()->get('request.url.pathname');
        $path       = realpath($root . $filename);

        if (!$path || strpos($path, $root) !== 0 || !is_file($path)) {
            $response->setStatusCode(Response::STATUS_CODE_404);
        } else {
            $response->setStream(fopen($path, 'r'));
            $response->getHeaders()->addHeader(new ContentType(mime_content_type($path)));
        }

        return $response;
    }
}
