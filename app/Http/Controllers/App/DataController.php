<?php

namespace App\Http\Controllers\App;

use App\Models\CarMake;
use App\Models\Category;
use App\Models\Duration;
use App\Models\MobileString;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\LocalizationController;
use App\Http\Resources\CarDataResourceCollection;
use App\Http\Resources\CategoryResourceCollection;
use App\Models\TermsCondition;

class DataController extends Controller
{
    use ApiResponser;
    
    public function categories(){

    	$categories = Category::all();
        return $this->sendResponse(new CategoryResourceCollection($categories), 'Category List.');
    	 
    }

    public function strings($lang)
    {
        return $this->sendResponse(LocalizationController::getString($lang), 'Translation Strings.');

    }

    public function appData()
    {
        $data=TermsCondition::select('type','title','description')->get();
        return $this->sendResponse($data, 'App Data');

    }

    public function testNotify()
    {
        //fcm notification
        try {
            return fcm()
                ->to([
                    'dtlqHKzrQGqc8-4FZB78OM:APA91bE8HxXe9HJKVBLb7r5PvxurMAlXxtQY1COl-6ii1DlbFxIk58HF9aUQNW9rpwiVcs8Mikod1s-XRrrraLkU6rZYsCEPIUTWpnQriN02kXdS2f5lW0DbPWGUKHgluOvWDh5Mjlia',
                ])
                ->priority('high')
                ->timeToLive(0)
                ->data([
                    'title' => 'Test Notification',
                    'body' => 'This is a test notification',
                ])->notification([
                    'title' => 'Test Notification',
                    'body' => 'This is a test notification',
                ])
            ->send();
        } catch (\Exception $e) {
            dd($e->getMessage( ));
        }
        


    }

}
