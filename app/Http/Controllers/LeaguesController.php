<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leagues;
use Inertia\Inertia;

class LeaguesController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Leagues/Index',[
            'status' => session('status'),
        ]);
    }
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Leagues::create($request->all());

        return redirect()->route('leagues.index');
    }
    public function list(Request $request)
    {
        // Retrieve search query from request
        $searchQuery = $request->search;

        // Query builder for leagues
        $query = Leagues::query();

        // Apply search filter if search query is provided
        if ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        }

         // Exclude league with ID 2
        $query->where('id', '!=', 2);

        // Get total count of records before pagination
        $totalCount = $query->count();

        // Set the number of records to display per page
        $perPage = 10;

        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $perPage);

        // Get the current page from the request, default to 1 if not provided
        $currentPage = $request->page_num;

        // Calculate the offset for pagination
        $offset = ($currentPage - 1) * $perPage;

        // Retrieve leagues data with pagination
        $leagues = $query->offset($offset)
                         ->limit($perPage)
                         ->get();

        return response()->json([
            'leagues' => $leagues,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'total_count' => $totalCount,
        ]);
    }

    // Update the specified resource in storage.
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $team = Leagues::findOrFail($request->id);
        $team->update($request->all());

        return redirect()->route('leagues.index');
    }

    // Remove the specified resource from storage.
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $team = Leagues::findOrFail($request->id);
        $team->delete();

        return redirect()->route('leagues.index');
    }
    public function dropdown(){
        $leagues = Leagues::all(['id', 'name']); // Fetch only id and name columns
        return response()->json($leagues);
    }
}
