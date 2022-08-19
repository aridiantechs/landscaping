<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Subscription;

class DashboardController extends Controller
{
    public function index()
    {
        $users=User::all()->count();
        $subscribers=Subscription::has('user')->has('plan')->count();
        $total_revenue=Subscription::has('user')->withCount(['plan'=>function($query){
            $query->select(\DB::raw('SUM(amount)'));
        }])->get()->sum('plan_count');
        
        return Inertia::render('Dashboard/Index', [
            'values' => [
                'users' => (string)$users,
                'subscribers' =>  (string)$subscribers,
                'total_revenue' =>  (string)'$'.$total_revenue,
            ],
        ]);
    }
}
