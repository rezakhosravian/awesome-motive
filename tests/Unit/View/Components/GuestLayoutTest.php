<?php

namespace Tests\Unit\View\Components;

use App\View\Components\GuestLayout;
use Illuminate\View\View;
use Tests\TestCase;

class GuestLayoutTest extends TestCase
{
    public function test_component_can_be_instantiated()
    {
        $component = new GuestLayout();
        
        $this->assertInstanceOf(GuestLayout::class, $component);
    }

    public function test_render_returns_view()
    {
        $component = new GuestLayout();
        
        $view = $component->render();
        
        $this->assertInstanceOf(View::class, $view);
    }

    public function test_render_returns_correct_view_name()
    {
        $component = new GuestLayout();
        
        $view = $component->render();
        
        // Check that the view name is 'layouts.guest'
        $this->assertEquals('layouts.guest', $view->getName());
    }

    public function test_component_inheritance()
    {
        $component = new GuestLayout();
        
        $this->assertInstanceOf(\Illuminate\View\Component::class, $component);
    }

    public function test_component_has_render_method()
    {
        $component = new GuestLayout();
        
        $this->assertTrue(method_exists($component, 'render'));
    }

    public function test_render_method_returns_view_instance()
    {
        $component = new GuestLayout();
        
        $result = $component->render();
        
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    }

    public function test_multiple_instances_work_independently()
    {
        $component1 = new GuestLayout();
        $component2 = new GuestLayout();
        
        $view1 = $component1->render();
        $view2 = $component2->render();
        
        $this->assertInstanceOf(View::class, $view1);
        $this->assertInstanceOf(View::class, $view2);
        $this->assertEquals($view1->getName(), $view2->getName());
    }

    public function test_component_can_be_used_in_blade()
    {
        // This test ensures the component follows Laravel's component contract
        $component = new GuestLayout();
        
        // The render method should return a view that can be rendered
        $view = $component->render();
        
        $this->assertTrue(method_exists($view, 'render'));
    }
}
