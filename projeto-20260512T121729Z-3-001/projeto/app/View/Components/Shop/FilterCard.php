<?php

namespace App\View\Components\Shop;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterCard extends Component
{
    public array $listCategories;
    public array $listConditions;
    /**
     * Create a new component instance.
     */
    public function __construct(public array $categories,
        public string $filterAction,
        public string $resetUrl,
        public ?int $category = null,
        public ?string $condition = null,
        public ?string $name = null,)
     {
        $this->listCategories = [null => 'Any category'] + $categories;
        $this->listConditions = [
            null => 'All',
            1 => 'Own',
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.shop.filter-card');
    }
}
