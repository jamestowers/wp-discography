var $;

$ = jQuery;

this.DiscographyPublic = (function() {
  function DiscographyPublic() {
    console.log('[DiscographyPublic] init');
    this.doc = $(document);
    this.addEventListeners();
  }

  DiscographyPublic.prototype.addEventListeners = function() {
    this.doc.ready(function() {
      return plyr.setup({
        controls: "play"
      });
    });
    return this.doc.on('click', '.toggle-lyrics', function() {
      $(this).parents('li').toggleClass('expanded-lyrics');
      return false;
    });
  };

  return DiscographyPublic;

})();

window.DiscographyPublic = new DiscographyPublic();
