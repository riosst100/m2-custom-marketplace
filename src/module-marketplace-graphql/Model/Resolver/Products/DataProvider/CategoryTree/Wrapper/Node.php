<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper;

/**
 * Category tree node wrapper.
 */
class Node {
    /**
     * @var int
     */
    private $id;

    /**
     * @var self[]
     */
    private $children = [];

    /**
     * @var self[]
     */
    private $parent = [];

    /**
     * @var array
     */
    private $modelData;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Set category model data for node.
     *
     * @param array|null $modelData
     *
     * @return $this
     */
    public function setModelData(?array $modelData): self
    {
        $this->modelData = $modelData;
        return $this;
    }

    /**
     * Add child node.
     *
     * @param Node $categoryTreeNode
     * @return $this
     */
    public function addChild(self $categoryTreeNode): self
    {
        $this->children[$categoryTreeNode->getId()] = $categoryTreeNode;
        return $this;
    }

    /**
     * Get array of children nodes.
     *
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Add parent node.
     *
     * @param Node $categoryTreeNode
     * @return $this
     */
    public function addParent(self $categoryTreeNode): self
    {
        $this->parent[$categoryTreeNode->getId()] = $categoryTreeNode;
        return $this;
    }

    /**
     * Get array of parent nodes.
     *
     * @return Node[]
     */
    public function getParent(): array
    {
        return $this->parent;
    }

    /**
     * Get node id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Render node and its children as an array recursively, returns null if node data is not set.
     *
     * @return array|null
     */
    public function renderArray(): ?array
    {
        if (!$this->modelData) {
            return null;
        }
        return array_merge(
            $this->modelData,
            [
                'children' => array_filter(
                    array_map(
                        function ($node) {
                            return $node->renderArray();
                        },
                        $this->children
                    )
                ),
                'parent' => array_filter(
                    array_map(
                        function ($node) {
                            return $node->renderArray();
                        },
                        $this->parent
                    )
                )
            ]
        );
    }
}
