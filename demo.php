<?php

require 'vendor/autoload.php';

$clientID = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$clientSecret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$callbackUrl = 'http://example.com/nuke-spotify-playlists/demo.php'; //Callback URL

$session = new SpotifyWebAPI\Session();

// Initialize API
$api = new SpotifyWebAPI\SpotifyWebAPI($clientID, $clientSecret, $callbackUrl); 

if (isset($_GET['code'])) {
    
    $session->requestAccessToken($_GET['code']);
    
    $api->setAccessToken($session->getAccessToken());
    
    echo json_encode($api->me());
    echo '<hr>';

} else {

    $scopes = array(
        'scope' => array(
            'playlist-read-private',
            'playlist-read-collaborative',
            'playlist-modify-public',
            'playlist-modify-private',
            'user-follow-modify',
            'user-follow-read',
            'user-library-read',
            'user-library-modify',
            'user-read-private',
            'user-read-birthdate',
            'user-read-email'
        ),
    );

    header('Location: ' . $session->getAuthorizeUrl($scopes));
}


$refreshToken = $session->getRefreshToken(); // Get Refresh Token

$session->refreshAccessToken($refreshToken); // Refresh Access Token

$accessToken = $session->getAccessToken(); // Get Access Token

// Set the new access token on the API wrapper
$api->setAccessToken($accessToken);

/* ------------------------------------------------------- *
    This is what does the actual deleting of playlists    
 * ------------------------------------------------------- */

// Set Defaults
$username = 'spotify_username';
$limit = 50;
$offset = 0;

while ($offset < 100) {
    
    $playlists = $api->getUserPlaylists($username, 
        array(
            'limit' => $limit,
            'offset' => $offset
        ));

    foreach ($playlists->items as $key => $playlist) {
        try {
            $unfollow_response = $api->unfollowPlaylist($username, $playlist->id); //'1YoeGhBLhSbZYGLpQcYHin');   
            echo 'Unfollowed Playlist ' . $key . ') ' . $playlist->id . ' ($unfollow_response)';
            echo '<br>';
        } catch (Exception $e) {
            echo '<!-- playlist ID: ' . $playlist->id;
            print_r($e);
            echo ' -->';
        }
        usleep(500000); //sleep 1/2s = rudimentary rate limit
    }
    $offset++;
}

//https://open.spotify.com/user/anthonylrivera/playlist/1YoeGhBLhSbZYGLpQcYHin | Kraftwerk
