<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::active();
        return view('public.services.index', compact('services'));
    }

    public function show(string $slug)
    {
        $service = Service::findBySlug($slug);
        if (!$service || !($service->is_active ?? true)) {
            abort(404);
        }
        $related = Service::related($service->slug, 3);
        return view('public.services.show', compact('service', 'related'));
    }
}
