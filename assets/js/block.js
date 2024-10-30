(function (blocks, element, blockEditor) {
  var el = element.createElement;
  var useBlockProps = blockEditor.useBlockProps;

  var blockStyle = {
    backgroundColor: '#000',
    color: '#fff',
    padding: '20px',
  };

  blocks.registerBlockType('hear-me/player', {
    apiVersion: 2,
    title: 'HearMe Player',
    icon: 'universal-access-alt',
    category: 'design',
    example: {},
    edit: function () {
      var blockProps = useBlockProps({ style: blockStyle });
      return el('div', blockProps, 'HEAR ME PLAYER');
    },
    save: function () {
      var blockProps = useBlockProps.save({ style: blockStyle });
      return el('div', {}, '[hear_me_player]');
    },
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor);
