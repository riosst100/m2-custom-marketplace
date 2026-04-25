<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Import;

use Magento\Framework\Option\ArrayInterface;

class Entity implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '', 'value' => ''],
            ['label' => 'Action Toys', 'value' => 'actiontoys'],
            ['label' => 'Action Toys Model Kits', 'value' => 'actiontoysmodelkits'],
            ['label' => 'Anime', 'value' => 'anime'],
            ['label' => 'Anime Model Kits', 'value' => 'animemodelkits'],
            ['label' => 'Fun Goods', 'value' => 'fungoods'],
            ['label' => 'Dolls', 'value' => 'dolls'],
            ['label' => 'Collectible Card Games', 'value' => 'cardgame'],
            ['label' => 'Living Card Games', 'value' => 'livingcardgame'],
            ['label' => 'Miniature Games', 'value' => 'miniaturegame'],
            ['label' => 'Games Workshop', 'value' => 'gamesworkshop'],
            ['label' => 'Sport Cards', 'value' => 'sportcard'],
            ['label' => 'Non Sports Cards', 'value' => 'nonsportcard'],
            ['label' => 'Bricks', 'value' => 'bricks'],
            ['label' => 'Diecast Vehicles', 'value' => 'diecastvehicles'],
            ['label' => 'Diecast Aircrafts', 'value' => 'diecastaircrafts'],
            ['label' => 'Diecast Military', 'value' => 'diecastmilitary'],
            ['label' => 'Model Kits Vehicles', 'value' => 'modelkitsvehicles'],
            ['label' => 'Model Kits Aircrafts', 'value' => 'modelkitsaircrafts'],
            ['label' => 'Model Kits Military', 'value' => 'modelkitsmilitary'],
            ['label' => 'Trains', 'value' => 'trains'],
        ];
    }
}