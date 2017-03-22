app.service( 'deepstream', function() {
    // var client = deepstream( 'localhost:6020' )
    // client.login({ username: 'ds-simple-input-' + client.getUid() });
    // return client;
    return deepstream( 'wss://154.deepstreamhub.com?apiKey=8a1f799e-b575-4bfc-a374-84e82f22cf12').login( {username: 'tracking-new'} );

    /*return deepstream( 'localhost:6020' )
        .login( {username: 'tracking-new'} );*/
});