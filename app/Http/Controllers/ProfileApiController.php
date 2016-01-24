<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\UpdatePodcastFromRss;
use App\Models\User;
use App\Models\Podcast;
use App\Services\FeedService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class ProfileApiController extends Controller
{

    /**
     * Set the user profile.
     *
     * @return Response
     */
    public function postProfile()
    {
        $user = Auth::user();

        $user->name = Input::get('name');
        $user->avatar = Input::get('avatar');
        $user->url = Input::get('url');
        $user->save();

        return $user->toJson();
    }

    // /**
    //  * Set visibility for podcast.
    //  *
    //  * @param  Request  $request
    //  * @return Response
    //  */
    // public function postSetVisibility(Request $request)
    // {
    //     $user = Auth::user();
    //     $podcast_id = Input::get('podcast');
    //     $visible = (bool) Input::get('visible');
    //
    //     $podcast = $user->podcasts()
    //         ->where('podcast_id', $podcast_id)
    //         ->withPivot('visible')
    //         ->firstOrFail();
    //
    //     $podcast->pivot->visible = $visible;
    //     $podcast->pivot->save();
    //
    //     return $podcast->toJson();
    // }

    /**
     * Set order for list of podcasts.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postUpdatePodcasts(Request $request)
    {
        $user = Auth::user();

        $podcasts = Input::get('podcasts');
        // $podcastIds = array_map(function($podcast) {
        //     return (int)$podcast['id'];
        // }, $podcastsJson);

        // $podcasts = $user->podcasts()
        //     ->whereIn('podcast_id', $podcast_ids)
        //     ->withPivot('visible', 'position');

        foreach ($podcasts as $podcast) {
            $user->podcasts()
                ->where('podcast_id', $podcast['id'])
                ->withPivot('visible', 'position', 'description')
                ->update([
                    'podcast_user.description' => $podcast['description'],
                    'visible' => $podcast['visible'],
                    'position' => $podcast['position']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Save podcasts from uploaded opml file.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postPodcastsByOpml(Request $request, FeedService $parser)
    {

        if (!$request->hasFile('xml')) {
            return response()->json(['error' => 'no file.']);
        } elseif (!$request->file('xml')->isValid()) {
            return response()->json(['error' => 'file invalid.']);
        }

        $user = Auth::user();
        $file = $request->file('xml');
        $feeds = $parser->parseOpml($file);
        $new = [];
        $pos = $user->podcasts()
            ->withPivot('position')
            ->max('position');
        $pos = $pos ? $pos : 0;
        $added = [];

        foreach ($feeds as $feed) {

            $podcast = Podcast::where('feed', $feed)->first();
            if (!$podcast) {
                $podcast = new Podcast;
                $podcast->feed = $feed;
                $podcast->save();

                // load feed details asynchronously
                $this->dispatch(new UpdatePodcastFromRss($podcast));
            }

            if (!$user->podcasts()->where('feed', $feed)->exists()) {
                $user->podcasts()->save($podcast, [
                    'position' => $pos,
                    'visible' => true]);
                $pos++;
                $new[] = $podcast;
            }
        }

        return response()->json(['success' => true, 'new' => $new, 'feeds' => $feeds]);
    }
}
