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
    

window.DiscographyPublic = new DiscographyPublic()