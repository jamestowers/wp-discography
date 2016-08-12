var $;

$ = jQuery;

this.DiscographyAdmin = (function() {
  function DiscographyAdmin() {
    console.log('[DiscographyAdmin] init');
    this.doc = $(document);
    this.artistName = 'M.I.A.';
    this.fieldMaps = {
      'lookupId': 'lookup-id',
      'previewUrl': 'preview-url',
      'releaseDate': 'release-date',
      'url': 'url'
    };
    this.addEventListeners();
  }

  DiscographyAdmin.prototype.addEventListeners = function() {
    var app;
    app = this;
    this.doc.on('click', '#itunes-search-btn', function() {
      var entity, entityType, title;
      title = $('input[name="post_title"]').val();
      if (title !== '') {
        entityType = $(this).data('entity');
        entity = entityType === 'track' ? 'song' : 'album';
        return app.searchItunes({
          'term': app.artistName + ' ' + title,
          'entity': entity,
          'media': 'music',
          'limit': 5
        });
      }
    });
    this.doc.on('click', '#search-results a', function() {
      var data;
      data = $(this).data();
      app.fillSelectedFields(data);
      return false;
    });
    this.doc.on('click', '#fetchTracks', function() {
      var albumId, lookupId;
      lookupId = $(this).data('lookup-id');
      albumId = $(this).data('album-id');
      app.fetchAlbumTracks(lookupId, albumId);
      return false;
    });
    this.doc.on('click', '.insert-track', function(e) {
      var data;
      data = $(this).data();
      app.insertTrack(data, e);
      return false;
    });
    this.doc.on('click', '#insert-all-tracks', function(e) {
      var $btns;
      $btns = $(this).parents('table').find('.insert-track');
      $btns.each(function(i, el) {
        var data;
        data = $(el).data();
        return $.when(app.insertTrack(data, el)).then(function() {
          return $(el).html('<span class="dashicons dashicons-yes"></span> Added');
        });
      });
      return false;
    });
    return this.doc.on('ready', function() {
      return plyr.setup();
    });
  };

  DiscographyAdmin.prototype.searchItunes = function(data) {
    console.log('[DiscographyAdmin] Searching iTunes');
    data.action = 'search_itunes';
    return $.ajax({
      url: ajax_object.ajax_url,
      data: data,
      type: 'post',
      beforeSend: function() {
        return $('.spinner').addClass('is-active');
      },
      success: (function(_this) {
        return function(html) {
          return $('#search-results').html(html).removeClass('hide');
        };
      })(this),
      error: function(xhr, status, error) {
        return console.error(error);
      },
      complete: function() {
        return $('.spinner').removeClass('is-active');
      }
    });
  };

  DiscographyAdmin.prototype.fetchAlbumTracks = function(lookupId, albumId) {
    var data;
    console.log('[DiscographyAdmin] Doing iTunes lookup');
    data = {
      'action': 'fetch_album_tracks',
      'id': lookupId,
      'entity': 'song',
      'album_id': albumId
    };
    return $.ajax({
      url: ajax_object.ajax_url,
      data: data,
      type: 'post',
      beforeSend: function() {
        return $('.spinner').addClass('is-active');
      },
      success: (function(_this) {
        return function(html) {
          $('#album-tracks').html(html).removeClass('hide');
          return plyr.setup();
        };
      })(this),
      error: function(xhr, status, error) {
        return console.error(error);
      },
      complete: function() {
        return $('.spinner').removeClass('is-active');
      }
    });
  };

  DiscographyAdmin.prototype.fillSelectedFields = function(data) {
    var fetchTracksLink, linkUrl, newUrl;
    $.each(this.fieldMaps, function(key, value) {
      if (data[key]) {
        if (value === 'release-date') {
          data[key] = data[key].replace(/T|Z/g, ' ');
        }
        return $('input[name="wp-discography_' + value + '"]').val(data[key]);
      }
    });
    fetchTracksLink = $('a#fetchTracks');
    if (fetchTracksLink.length && data['lookupId']) {
      linkUrl = fetchTracksLink.attr('href');
      newUrl = linkUrl.replace(/id=.*/, 'id=' + data['lookupId']);
      return fetchTracksLink.attr('href', newUrl);
    }
  };

  DiscographyAdmin.prototype.insertTrack = function(data, e) {
    if (e == null) {
      e = null;
    }
    data.action = 'insert_track';
    return $.ajax({
      url: ajax_object.ajax_url,
      data: data,
      type: 'post',
      beforeSend: function() {
        return $(e.target).next('.spinner').addClass('is-active');
      },
      success: (function(_this) {
        return function(html) {
          return console.log(html);
        };
      })(this),
      error: function(xhr, status, error) {
        return console.error(error);
      },
      complete: function() {
        return $(e.target).next('.spinner').removeClass('is-active');
      }
    });
  };

  return DiscographyAdmin;

})();

window.DiscographyAdmin = new DiscographyAdmin();
