@extends('layouts.navbar')
@section('title', 'Settings')

@section('head')
<script type="text/javascript">
    window.$user = <?php echo json_encode($user) ?>;
</script>
<script src="/assets/dist/settings.js"></script>
@stop

@section('content')

    <div class="uk-container uk-container-center uk-margin-top">

        <div class="uk-panel uk-panel-box">

            <div class="uk-grid" id="settings">
                <div class="uk-width-1-4">
                    <p>
                        <img class="uk-border-circle" width="180" height="180" src="{{ $user->avatar }}" alt="">
                    </p>
                    <button type="button" class="uk-button uk-button-link">Upload new image</button>
                </div>

                <div class="uk-width-3-4">

                    <h2>Profile Details</h2>

                    <form class="uk-form uk-form-stacked" action="" method="">
                        <div class="uk-form-row">
                            <label class="uk-form-label" for="name">Name</label>
                            <div class="uk-form-controls">
                                <input type="text" name="name" v-model="user.name">
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-form-label" for="website">Website</label>
                            <div class="uk-form-controls">
                                <input type="text" name="website" v-model="user.url">
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <div class="uk-form-controls">
                                <button class="uk-button uk-button-primary" @click.prevent="save">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>


        </div>

        <hr class="uk-grid-divider">

        <div class="uk-panel uk-panel-box">

            <h2>Upload something. Doesn't do anything. Joke is on you.</h2>

            <form action="{{ URL::route('api::postProfile') }}" method="post" enctype="multipart/form-data" class="uk-form">
                <input type="file" name="upload" value="">
                <input type="submit">
            </form>

        </div>

        <hr class="uk-grid-divider">

        <div class="uk-panel uk-panel-box">
            <form action="{{ URL::route('api::postPodcastsByOpml') }}" method="post" enctype="multipart/form-data"  class="uk-form">
                <h2>Upload OPML</h2>

                {{ csrf_field() }}
                <input type="file" name="xml" value="">
                <button  class="uk-button uk-button-primary">Upload</button>
            </form>
        </div>

    </div>

@stop
