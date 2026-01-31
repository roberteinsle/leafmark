<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyController extends Controller
{
    /**
     * Display family overview
     */
    public function index()
    {
        $user = Auth::user();
        $family = $user->family;

        return view('family.index', compact('user', 'family'));
    }

    /**
     * Show form to create a new family
     */
    public function create()
    {
        // Check if user already has a family or owns a family
        if (Auth::user()->hasFamily()) {
            return redirect()->route('family.index')->with('error', __('app.family.already_in_family'));
        }

        if (Auth::user()->ownsFamily()) {
            return redirect()->route('family.index')->with('error', __('app.family.already_owns_family'));
        }

        return view('family.create');
    }

    /**
     * Store a newly created family
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check if user already has a family
        if (Auth::user()->hasFamily()) {
            return back()->withErrors(['error' => __('app.family.already_in_family')]);
        }

        if (Auth::user()->ownsFamily()) {
            return back()->withErrors(['error' => __('app.family.already_owns_family')]);
        }

        // Create family
        $family = Family::create([
            'name' => $request->name,
            'owner_id' => Auth::id(),
        ]);

        // Add user to family
        Auth::user()->update(['family_id' => $family->id]);

        return redirect()->route('family.index')->with('success', __('app.family.family_created'));
    }

    /**
     * Show form to join a family
     */
    public function showJoinForm()
    {
        // Check if user already has a family
        if (Auth::user()->hasFamily()) {
            return redirect()->route('family.index')->with('error', __('app.family.already_in_family'));
        }

        return view('family.join');
    }

    /**
     * Join a family with code
     */
    public function join(Request $request)
    {
        $request->validate([
            'join_code' => 'required|string|size:8',
        ]);

        // Check if user already has a family
        if (Auth::user()->hasFamily()) {
            return back()->withErrors(['join_code' => __('app.family.already_in_family')]);
        }

        // Find family by join code
        $family = Family::where('join_code', strtoupper($request->join_code))->first();

        if (!$family) {
            return back()->withErrors(['join_code' => __('app.family.invalid_code')]);
        }

        // Add user to family
        Auth::user()->update(['family_id' => $family->id]);

        return redirect()->route('family.index')->with('success', __('app.family.joined_family', ['name' => $family->name]));
    }

    /**
     * Leave current family
     */
    public function leave()
    {
        $user = Auth::user();

        if (!$user->hasFamily()) {
            return redirect()->route('family.index')->with('error', __('app.family.not_in_family'));
        }

        $family = $user->family;

        // Check if user is the owner
        if ($family->isOwner($user)) {
            // Check if there are other members
            if ($family->member_count > 1) {
                return back()->with('error', __('app.family.owner_cannot_leave'));
            }

            // Delete family if owner is the only member
            $family->delete();
        } else {
            // Just leave the family
            $user->leaveFamily();
        }

        return redirect()->route('family.index')->with('success', __('app.family.left_family'));
    }

    /**
     * Delete the family (owner only)
     */
    public function destroy()
    {
        $user = Auth::user();
        $family = $user->family;

        if (!$family) {
            return redirect()->route('family.index')->with('error', __('app.family.not_in_family'));
        }

        if (!$family->isOwner($user)) {
            return redirect()->route('family.index')->with('error', __('app.family.only_owner_can_delete'));
        }

        $family->delete();

        return redirect()->route('family.index')->with('success', __('app.family.family_deleted'));
    }

    /**
     * Regenerate join code (owner only)
     */
    public function regenerateCode()
    {
        $user = Auth::user();
        $family = $user->family;

        if (!$family) {
            return redirect()->route('family.index')->with('error', __('app.family.not_in_family'));
        }

        if (!$family->isOwner($user)) {
            return redirect()->route('family.index')->with('error', __('app.family.only_owner_can_regenerate'));
        }

        $family->update(['join_code' => Family::generateUniqueJoinCode()]);

        return redirect()->route('family.index')->with('success', __('app.family.code_regenerated'));
    }
}
