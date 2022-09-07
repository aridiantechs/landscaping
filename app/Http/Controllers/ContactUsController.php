<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ContactUs;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request as HttpRequest;

class ContactUsController extends Controller
{
    public function index()
    {
        return Inertia::render('ContactUs/Index', [
            'filters' => Request::all('search', 'trashed'),
            'contact_us' => ContactUs::latest()
                ->filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(HttpRequest $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contact_us',
            'phone' => 'required|string|max:255|unique:contact_us',
            'message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }

        $contact_us = new ContactUs();
        $contact_us->name = $request->name;
        $contact_us->email = $request->email;
        $contact_us->phone = $request->phone;
        $contact_us->message = $request->message;
        $contact_us->save();

        return Redirect::route('contact-us')
            ->with('success', 'Data submitted successfully.');
    }

    public function edit(Organization $organization)
    {
        return Inertia::render('Organizations/Edit', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address' => $organization->address,
                'city' => $organization->city,
                'region' => $organization->region,
                'country' => $organization->country,
                'postal_code' => $organization->postal_code,
                'deleted_at' => $organization->deleted_at,
                'contacts' => $organization->contacts()->orderByName()->get()->map->only('id', 'name', 'city', 'phone'),
            ],
        ]);
    }

    public function update(Organization $organization)
    {
        $organization->update(
            Request::validate([
                'name' => ['required', 'max:100'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
            ])
        );

        return Redirect::back()->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return Redirect::back()->with('success', 'Organization deleted.');
    }

    public function restore(Organization $organization)
    {
        $organization->restore();

        return Redirect::back()->with('success', 'Organization restored.');
    }
}