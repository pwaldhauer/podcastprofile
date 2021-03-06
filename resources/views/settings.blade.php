@extends('layouts.navbar')
@section('title', 'Settings')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script type="text/javascript">
    window.$user = <?php echo json_encode($user) ?>;
    window.$podcasts = <?php echo $user->podcasts()->get()->toJson() ?>;
    window.$routes = {
        savePodcasts: "{{ URL::route('api::podcasts') }}"
    }
</script>
@stop

@section('content')

<div class="uk-container uk-container-center uk-margin-large-top">

        <div id="settings">

            <div class="uk-panel uk-panel-box">

                <div class="uk-grid">
                    <div class="uk-width-1-4 uk-text-center">
                        <p>
                            <img class="uk-border-circle" width="180" height="180" src="{{ $user->avatar }}" alt="">
                        </p>

                        <form action="{{ URL::route('api::profile::image') }}" method="post" enctype="multipart/form-data" v-el="avatarform">

                            {{ csrf_field() }}

                            <input type="file" name="image" @change="uploadavatar" class="site-file-input" />
                            <button type="submit" class="uk-button uk-button-link">Upload</button>
                        </form>
                    </div>

                    <div class="uk-width-3-4">

                        <form class="uk-form uk-form-stacked" action="{{ URL::route('api::profile') }}" @submit.prevent="save($event)">
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="name">Name</label>
                                <div class="uk-form-controls">
                                    <input type="text" name="name" class="uk-form-large uk-width-1-1" placeholder="Your real name" v-model="user.name">
                                </div>
                            </div>

                            <div class="uk-form-row">
                                <label class="uk-form-label" for="url">Website</label>
                                <div class="uk-form-controls">
                                    <input type="url" name="url" class="uk-form-large uk-width-1-1" placeholder="http://" v-model="user.url">
                                </div>
                            </div>

                            <div class="uk-form-row">
                                <div class="uk-form-controls uk-align-right">
                                    <button class="uk-button uk-button-primary uk-button-large">Save profile</button>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>
            </div>

        @if($user->podcastsPublic->count() > 0)

            <!-- This is the tabbed navigation containing the toggling elements -->
            <ul class="uk-tab uk-margin-large-top" data-uk-tab="{connect:'#lists'}">
                <li><a href="">Manage Podcasts</a></li>
                <li><a href="">Reorder</a></li>
            </ul>

            <!-- This is the container of the content items -->
            <ul id="lists" class="uk-switcher uk-margin">
                <li>

                    <div class="uk-panel uk-panel-box" v-if="!podcasts.length">
                        Please upload a list of podcasts below.
                    </div>

                    <ul class="uk-sortable uk-list uk-list-space" v-if="podcasts.length">
                        <li v-for="podcast in podcasts">
                            <div class="uk-panel uk-panel-box">
                                <div class="uk-grid">
                                    <div class="uk-width-1-4">
                                        <img :src="podcast.coverimage" width="150" height="150" alt="Podcast cover" />
                                    </div>
                                    <div class="uk-width-3-4">
                                        <h3>Say something about <strong>@{{{ podcast.name }}}</strong>:</h3>
                                        <textarea class="uk-width-1-1 site-podcast-comment" rows="4" v-model="podcast.comment"></textarea><br>
                                        <input type="checkbox" v-model="podcast.visible"> Show in list

                                        <p class="uk-text-right">
                                            <button class="uk-button uk-button-primary" @click="savePodcasts">Save</button>
                                        </p>

                                    </div>
                                </div>

                            </div>
                        </li>
                    </ul>


                </li>
                <li>

                    <p class="uk-text-right" v-if="podcasts.length">
                        <button class="uk-button uk-button-primary" @click="savePodcasts">Save</button>
                    </p>
                    <div class="uk-panel uk-panel-box" v-else>
                        You have to upload podcasts before you can reorder them.
                    </div>

                    <ul class="uk-sortable uk-list uk-list-space" v-el:list>
                        <li v-for="podcast in podcasts" data-id="@{{ podcast.id }}">
                            <div class="uk-panel uk-panel-box">
                                <div class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right"></div>
                                <strong>@{{{ podcast.name }}}</strong>
                            </div>
                        </li>
                    </ul>

                    <p class="uk-text-right" v-if="podcasts.list">
                        <button class="uk-button uk-button-primary" @click="savePodcasts">Save</button>
                    </p>

                </li>
            </ul>
        </div>

    @endif

    <hr class="uk-grid-divider">

    <div class="uk-panel uk-panel-box" id="upload-opml">
        <form action="{{ URL::route('api::postPodcastsByOpml') }}" method="post" enctype="multipart/form-data" class="uk-form" @submit.prevent="performupload($event)" v-el="form">

            <pre>@{{ demo | json }}</pre>

            <h2>Upload your podcasts</h2>

            <ol class="uk-text-large">
                <li>Open your podcast client or app</li>
                <li>Export the list of podcasts (this should be an <code>*.opml</code> or <code>*.xml</code> file)</li>
                <li>Select and upload that file below.</li>
            </ol>

            {{ csrf_field() }}

            <p class="uk-margin uk-alert uk-alert-note" v-if="uploading">
                <img src="/assets/loading-small.gif" alt="Loading" /> Uploading... Please stand by.
            </p>
            <p class="uk-margin uk-alert uk-alert-success" v-if="uploaded">
                Thanks! We are now processing the upload. Your podcasts will appear here in the settings and on your profile. This will take <strong>a few moments</strong>. Hit refresh whenever you feel like it.
            </p>
            <div class="uk-form" v-if="!(uploading || uploaded)">
                <input type="file" name="xml" value="" class="" v-el:xml>
                <p class="uk-text-right">
                    <button class="uk-button uk-button-large uk-button-primary">Upload</button>
                </p>
            </div>
        </form>
    </div>

</div>

<script src="/assets/dist/settings.js"></script>

@stop
