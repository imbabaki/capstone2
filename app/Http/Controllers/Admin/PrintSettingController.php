<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrintSetting;

class PrintSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $settings = PrintSetting::all(); // fetch all records
    return view('admin.print-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.print-settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $request->validate([
        'paper_size' => 'required|string',
        'color_option' => 'required|string',
        'price' => 'required|numeric',
    ]);

    $exists = PrintSetting::where('paper_size', $request->paper_size)
        ->where('color_option', $request->color_option)
        ->exists();

    if ($exists) {
        return redirect()->back()->with('error', 'This print setting already exists.');
    }

    PrintSetting::create([
        'paper_size' => $request->paper_size,
        'color_option' => $request->color_option,
        'price' => $request->price,
    ]);

    return redirect()->route('admin.print-settings.index')->with('success', 'Print setting added!');
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $setting = PrintSetting::findOrFail($id);
        return view('admin.print-settings.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $setting = PrintSetting::findOrFail($id);
        return view('admin.print-settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'paper_size' => 'required|string',
            'color_option' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $setting = PrintSetting::findOrFail($id);
        $setting->update($request->all());

        return redirect()->route('admin.print-settings.index')->with('success', 'Print setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $setting = PrintSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.print-settings.index')->with('success', 'Print setting deleted successfully.');
    }
}