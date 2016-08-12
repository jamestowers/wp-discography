$ = jQuery

class @DiscographyAdmin

  constructor:->
    console.log '[DiscographyAdmin] init'
    @doc = $(document)
    @artistName = 'M.I.A.'
    @fieldMaps = {
      'lookupId': 'lookup-id'
      'previewUrl': 'preview-url'
      'releaseDate': 'release-date'
      'url': 'url'
    }
    @addEventListeners()

  addEventListeners: ->

    app = @

    @doc.on 'click', '#itunes-search-btn', ->
      title = $('input[name="post_title"]').val()
      if title isnt ''
        entityType = $(this).data('entity')
        entity = if entityType is 'track' then 'song' else 'album'
        app.searchItunes(
          'term': app.artistName + ' ' + title
          'entity': entity
          'media': 'music'
          'limit': 5
          )

    @doc.on 'click', '#search-results a', ->
      data = $(this).data()
      app.fillSelectedFields(data)
      false

    @doc.on 'click', '#fetchTracks', ->
      lookupId = $(this).data('lookup-id')
      albumId = $(this).data('album-id')
      app.fetchAlbumTracks(lookupId, albumId)
      false

    @doc.on 'click', '.insert-track', (e)->
      data = $(this).data()
      app.insertTrack(data, e)
      false

    @doc.on 'click', '#insert-all-tracks', (e)->
      $btns = $(this).parents('table').find('.insert-track')
      $btns.each (i, el)->
        data = $(el).data()
        $.when( app.insertTrack(data, el) ).then( ->
          $(el).html '<span class="dashicons dashicons-yes"></span> Added'
          )
      false

    @doc.on 'ready', ->
      plyr.setup();

  searchItunes: (data)->
    console.log '[DiscographyAdmin] Searching iTunes'
    data.action = 'search_itunes'
    $.ajax
      url: ajax_object.ajax_url
      data: data
      type: 'post'
      beforeSend: ->
        $('.spinner').addClass 'is-active'
      success: (html) =>
        $('#search-results').html(html).removeClass 'hide'
      error: (xhr, status, error)->
        console.error error
      complete: ->
        $('.spinner').removeClass 'is-active'

  fetchAlbumTracks: (lookupId, albumId)->
    console.log '[DiscographyAdmin] Doing iTunes lookup'
    data = 
      'action': 'fetch_album_tracks'
      'id': lookupId
      'entity': 'song' 
      'album_id': albumId
    $.ajax
      url: ajax_object.ajax_url
      data: data
      type: 'post'
      beforeSend: ->
        $('.spinner').addClass 'is-active'
      success: (html) =>
        $('#album-tracks').html(html).removeClass 'hide'
        plyr.setup();
      error: (xhr, status, error)->
        console.error error
      complete: ->
        $('.spinner').removeClass 'is-active'


  fillSelectedFields: (data)->
    $.each @fieldMaps, (key, value) ->
      if data[key]
        if value is 'release-date'
          data[key] = data[key].replace(/T|Z/g,' ')
        $('input[name="wp-discography_' + value + '"]').val(data[key])
    
    fetchTracksLink = $('a#fetchTracks')
    if fetchTracksLink.length and data['lookupId']
      linkUrl = fetchTracksLink.attr 'href'
      newUrl = linkUrl.replace(/id=.*/, 'id=' + data['lookupId'])
      fetchTracksLink.attr('href', newUrl)

  insertTrack: (data, e = null)->
    data.action = 'insert_track'
    $.ajax
      url: ajax_object.ajax_url
      data: data
      type: 'post'
      beforeSend: ->
        $(e.target).next('.spinner').addClass 'is-active'
      success: (html) =>
        console.log html
      error: (xhr, status, error)->
        console.error error
      complete: ->
        $(e.target).next('.spinner').removeClass 'is-active'

window.DiscographyAdmin = new DiscographyAdmin()