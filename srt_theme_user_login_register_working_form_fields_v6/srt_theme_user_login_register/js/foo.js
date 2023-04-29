Drupal.behaviors.example_hello = {
    attach: function (context, settings) {

      // Attach a click listener to the clear button.
      var clearBtn = document.getElementById('edit-clear');
      clearBtn.addEventListener('click', function() {

          // Do something!
          console.log('Clear button clicked!');

      }, false);

    }
  };
