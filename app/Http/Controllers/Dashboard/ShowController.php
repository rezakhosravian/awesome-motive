<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Contracts\Service\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Show the application dashboard.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $stats = $this->userService->getUserStats($user);

        return view('dashboard', compact('stats'));
    }
} 