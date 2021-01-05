/*
*  Take field values, calculate quartals and YTD.
*  Check if output value not difference 0.5.
* */
(function ($, Drupal) {
  Drupal.behaviors.calculate = {
    attach: function (context, settings) {
      // Attach function to all inputs.
      $("#form-ever tr td input").once('calculate').on('change', function (event) {
        let el_tr = $(event.target);
        // Cell
        let el_cell = el_tr.parent().parent();
        // Input
        let el_val = Number(el_tr.val());
        let index = el_cell.index();

        // Calculate periods and year cells.
        function calcOutput(periods) {
          return (periods.reduce((a, b) => a + b, 0) + 1) / periods.length;
        }

        // Check if value sets by user, not different than 0.05.
        function customNumber(values) {
          let userValue = calcOutput(values);
          if (Math.abs(userValue - el_val) > 0.05) {
            el_tr.val(userValue.toFixed(2));
          }
        }

        // Function for get values and write it to array.
        function writeValues() {
          // Loop value.
          let loop = 3;
          let values = [];
          if (index <= 16) {
            loop = 2;
          }
          // Storing values.
          for (let i = loop; i >= 0; i--) {
            if (index === 17) {
              let n = 16 - 4 * (i);
              el_cell = $(el_cell.siblings()[n]);
            } else {
              el_cell = el_cell.prev();
            }
            values[i] = Number($($(el_cell.children()[0]).children()[0]).val());
          }
          return values;
        }

        /*
        * If month cell was triggered ->
        * check value and trigger the year input for update its value.
        * */
        if ((index % 4) === 0) {
          customNumber(writeValues());

          $($($(el_cell.siblings()[16]).children()[0]).children()[0]).triggerHandler('change');
        } else if (index === 17) {
          customNumber(writeValues());
          // If the year input was triggered just checks its value.
        } else {
          /*
          * If month input was triggered.
          * */
          let month = $(el_cell.siblings()[(((index / 4 >> 0) + 1) * 4) - 1]);
          $($(month.children()[0]).children()[0]).triggerHandler('change');
        }
      });
    }
  };
})(jQuery, Drupal);
