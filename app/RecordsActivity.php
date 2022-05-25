<?php

namespace App;

use App\Models\Activity;
use Illuminate\Support\Arr;

trait RecordsActivity
{
    public $oldAttributes = [];

    /**
     * automatically trigger once this trait is attached to an eloquent model
     */
    public static function bootRecordsActivity()
    {
        foreach (self::recordableEvents() as $event) {

            static::$event(function($model) use ($event){

                $model->recordActivity($model->activityDescription($event));

            });

            if ($event == 'updated') {

                static::updating(function($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }
        }
    }

    protected function activityDescription($description)
    {

        return "{$description}_". strtolower(class_basename($this));
    }


    protected static function recordableEvents()
    {
        if (isset(static::$recordableEvents)) {

            return static::$recordableEvents;
            # code...
        } 
           
        return ['created', 'updated'];

    }

    public function recordActivity($description)
    {

        $this->activity()->create([

            'user_id' => $this->activityOwner()->id,
            'description' => $description,
            'changes' => $this->activityChanges(),
            'project_id' => class_basename($this) == 'Project' ? $this->id : $this->project_id
            
        ]);
    }

    protected function activityOwner()
    {

        $project = $this->project ?? $this;

        return $project->owner;
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }
    
    public function activityChanges()
    {

        if ($this->wasChanged())
        {
            return [
                'before' => Arr::except(array_diff($this->oldAttributes, $this->getAttributes()), 'updated_at'),
                'after' => Arr::except($this->getChanges(), 'updated_at')
            ];
        }

    }

}