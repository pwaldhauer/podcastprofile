<?php

namespace App\Models;

use App\Jobs\UpdatePodcastFromRss;
use DB;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    /**
     * The users that belong to the podcast.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User')
            ->withPivot('position', 'description', 'visible')
            ->withTimestamps();
    }

    /**
     * Get the top Podcasts.
     *
     * @param int $count
     * @return List
     */
    public static function getTop($count)
    {
        $podcasts = Podcast::join('podcast_user', 'podcast_user.podcast_id', '=', 'podcasts.id')
            ->groupBy('podcasts.id')
            ->orderBy(DB::raw('count(podcast_user.id)'), 'desc')
            ->take($count)
            ->get([
                'podcasts.name',
                'podcasts.url',
                'podcasts.feed',
                'podcasts.coverimage',
                'podcasts.description',
                DB::raw('count(podcast_user.id) as podcast_user_count')]);

        return $podcasts;
    }

    /**
     * Get podcast from feed.
     * Create new podcast if necessary.
     *
     * @param string $feed
     * @return Podcast
     */
    public static function getOrCreateFromRss($feed)
    {
        $created = false;
        $podcast = Podcast::where('feed', $feed)->first();
        if (!$podcast) {
            $podcast = new Podcast;
            $podcast->feed = $feed;
            $podcast->coverimage = '/assets/default.png';
            $podcast->save();

            // load feed details asynchronously
            dispatch(new UpdatePodcastFromRss($podcast));
            $created = true;
        }
        return $podcast;
    }
}
