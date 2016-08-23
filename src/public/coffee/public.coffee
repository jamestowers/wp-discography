$ = jQuery

class @DiscographyPublic

  constructor:->
    console.log '[DiscographyPublic] init'
    @doc = $(document)
    @addEventListeners()

  addEventListeners: ->

    @doc.ready ->
      plyr.setup(
        controls: "play"
      )

    @doc.on 'click', '.toggle-lyrics', ->
      $(this).parents('li').toggleClass 'expanded-lyrics'
      false
    

window.DiscographyPublic = new DiscographyPublic()