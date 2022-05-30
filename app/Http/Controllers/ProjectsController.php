<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    
    public function index()
    {
        $projects = auth()->user()->accessibleProjects();

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store()
    {
        // validate
        $attributes = $this->validateRequest();
       
        $attributes['owner_id'] = auth()->id();

        // create
        $project = Project::create($attributes);
        
        if (request()->wantsJson()) {
            
            return ['message' => $project->path()];
        }

        // redirect
        return redirect($project->path());
    }


    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Project $project)
    {
        $this->authorize('update', $project);

        $project->update($this->validateRequest());

        return redirect($project->path());

    }

    public function destroy(Project $project)
    {
        $this->authorize('manage', $project);
        
        $project->delete();
        return redirect('/projects');
    }

    /**
     *  @return array
     */
    protected function validateRequest()
    {
        return request()->validate([
            'title' => 'sometimes|required',
            'description' => 'sometimes|required',
            'notes' => 'nullable'
            
        ]);
    }
}
