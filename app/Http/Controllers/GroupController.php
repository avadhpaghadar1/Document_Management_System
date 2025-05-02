<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    use AuthorizesRequests;
    public function display(Request $request)
    {
        try {
            $this->authorize('view_group');
            $sort_by = $request->input('sort_by', 'id');
            $sort_order = $request->input('sort_order', 'asc');
            $search = $request->input('search');
            $query = Group::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            $groups = $query->orderBy($sort_by, $sort_order)->paginate(5);
            return view('main/groups', ['groups' => $groups, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'search' => $search]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function view($id)
    {
        try {
            $this->authorize('view_group');
            $group = Group::find($id);
            $users = $group->users;
            return view('main/view-group', ['group' => $group, 'users' => $users]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function create()
    {
        try {
            $this->authorize('create_group');
            $users = User::get();
            return view('main/add-group', ['users' => $users]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function store(Request $request)
    {
        $users = $request->input('users');

        $validated = $request->validate([
            'name' => 'required|string|unique:groups,name'
        ]);
        $loggedInUserId = Auth::id();
        try {
            $group = Group::create([
                'name' => $validated['name'],
                'user_id' => $loggedInUserId
            ]);

            if ($group) {
                $group->users()->attach($users);
                return redirect()->to('groups')->with('success', 'Group Created Successfully.');
            } else {
                return redirect()->back()->with('error', 'Group creation failed.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('edit_group');
            $group = Group::find($id);
            $users = User::get();
            $members = $group->users;
            return view('main/update-group', ['group' => $group, 'users' => $users, 'members' => $members]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function update(Request $request, $id)
    {
        $users = $request->input('users');
        $name = $request->input('name');
        $validated = $request->validate([
            'name' => 'required|string|unique:groups,name,' . $id
        ]);
        try {
            $group = Group::find($id);
            $group->name = $validated['name'];
            $group->save();

            $group->users()->sync($users);

            return redirect()->to('groups')->with('success', 'Group Updated Successfully.');
        } catch (\Exception $e) {
            return redirect()->to('groups')->with('success', 'Group Updated Failed.');
        }
    }
    public function delete(Request $request)
    {

        try {
            $this->authorize('delete_group');
            $groupid = $request->input('delete');
            $group = Group::find($groupid);
            if ($group->delete()) {
                return redirect()->to('groups')->with('success', 'Group Deleted Successfully.');
            } else {
                return redirect()->to('groups')->with('error', 'Group Deleted Failed.');
            }
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
}
