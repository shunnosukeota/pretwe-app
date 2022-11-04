<?php

namespace App\Services;

use App\Models\Tweet;
use Carbon\Carbon;
use App\Models\Image;
use App\Models\Rating;
use App\Models\User;
use App\Modules\ImageUpload\ImageManagerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TweetService
{
    public function __construct(private ImageManagerInterface $imageManager)
    {}
    
    public function getTweets()
    {
        return Tweet::with('images')->orderBy('created_at', 'DESC')->Paginate(15);   
    }
    
    // 自分のtweetかどうかをチェックするメソッド
    public function checkOwnTweet(int $userId, int $tweetId): bool
    {
        $tweet = Tweet::where('id', $tweetId)->first();
        if (!$tweet) {
            return false;
        }

        return $tweet->user_id === $userId;
    }
    
    public function checkRating(int $userId, int $tweetId): bool
    {
        $tweet = Tweet::where('id', $tweetId)->first();
        

        foreach($tweet->ratings as $rating)
        {
            if (!$rating->raterId) {
                return false;
            }
            if(\Illuminate\Support\Facades\Auth::id() == $rating->raterId)
            {
                return false;
            }
        }
        return true;
    }

    public function countYesterdayTweets(): int
    {
        return Tweet::whereDate('created_at', '>=', Carbon::yesterday()->toDateTimeString())
            ->whereDate('created_at', '<', Carbon::today()->toDateTimeString())
            ->count();
    }
    public function saveTweet(int $userId, string $gift, string $present, string $relation, string $age, string $situation, string $detail, array $images)
    {
        DB::transaction(function () use ($userId, $gift, $present, $relation, $age, $situation, $detail, $images) {
            $tweet = new Tweet;
            $tweet->user_id = $userId;
            $tweet->gift = $gift;
            $tweet->present = $present;
            $tweet->relation = $relation;
            $tweet->age = $age;
            $tweet->situation = $situation;
            $tweet->detail = $detail;
            $tweet->save();

            foreach ($images as $image) {
                $name = $this->imageManager->save($image);
                $imageModel = new Image();
                $imageModel->name = $name;
                $imageModel->save();
                $tweet->images()->attach($imageModel->id);
            }
        });
    }
    public function deleteTweet(int $tweetId)
    {
        DB::transaction(function () use ($tweetId) {
            $tweet = Tweet::where('id', $tweetId)->firstOrFail();
            $tweet->images()->each(function ($image) use ($tweet){
                $this->imageManager->delete($image->name);
                $tweet->images()->detach($image->id);
                $image->delete();
            });
            $tweet->ratings()->each(function ($rate) use ($tweet){
                $tweet->ratings()->detach($rate->id);
                $rate->delete();
            });
    
            $tweet->delete();
        });
    }

}