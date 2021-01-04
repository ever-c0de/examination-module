/*
*  Take field values, calculate quartals and YTD.
*  Check if output value not difference 0.5.
* */
(function ($, Drupal) {
  Drupal.behaviors.calcSummary = {
    attach: function (context, settings) {
      // Attach function to all inputs.
      $("#form-ever tr td input").once('calcSummary').on('change', function (event) {
        let el_tr = $(event.target); // Input action.
        let el_cell = el_tr.parent().parent(); // The father of input
        let el_val = Number(el_tr.val()); // The value of input.
        let index = el_cell.index();

        // Calculate periods and year cells.
        function calcOutput(periods) {
          return (periods.reduce((a, b) => a + b, 0) + 1) / periods.length;
        }

        // Get values and write it into array.
        function getValues() {
          let values = [];
          let current = el_cell;
          // This need for set how many loops needed.
          let q = 3;
          if (index < 17) {
            q = 2;
          }
          // Storing values.
          for (let i = q; i >= 0; i--) {
            if (index === 17) {
              let n = 16 - 4 * (i);
              current = $(el_cell.siblings()[n]);
            } else {
              // If getting month values just walk back to previous 3 cells.
              current = current.prev();
            }
            values[i] = Number($($(current.children()[0]).children()[0]).val());
          }
          return values;
        }

        // Check if value sets by user, not different than 0.05.
        function checkAndSet(values) {
          let tmpTotal = calcOutput(values);
          if (Math.abs(tmpTotal - el_val) > 0.05) {
            el_tr.val(tmpTotal.toFixed(2));
          }
        }

        /*
        * If quarter cell was triggered ->
        * check value and trigger the year input for update its value.
        * */
        if ((index % 4) === 0) {
          checkAndSet(getValues());
          $($($(el_cell.siblings()[16]).children()[0]).children()[0]).triggerHandler('change');
        } else if (index === 17) {
          // If the year input was triggered just checks its value.
          checkAndSet(getValues());
        } else {
          /*
          * If a month input was triggered ->
          * trigger the handler for the appropriate quarter input.
          * */
          let quarter = $(el_cell.siblings()[(((index / 4 >> 0) + 1) * 4) - 1]);
          $($(quarter.children()[0]).children()[0]).triggerHandler('change');
        }
      });
    }
  };
})(jQuery, Drupal);
