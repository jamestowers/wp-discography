var $;

$ = jQuery;

this.DiscographyPublic = (function() {
  function DiscographyPublic() {
    console.log('[DiscographyPublic] init');
    this.doc = $(document);
    this.addEventListeners();
  }

  DiscographyPublic.prototype.addEventListeners = function() {
    return this.doc.ready(function() {
      return plyr.setup({
        controls: "play"
      });
    });
  };

  return DiscographyPublic;

})();

window.DiscographyPublic = new DiscographyPublic();
