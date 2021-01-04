// Handler on input values change to calculate quarter and year values
// and not to allow adjust it more than 0.05.
(function ($, Drupal) {
  Drupal.behaviors.calcSummary = {
    attach: function (context, settings) {
      // Attach it to all inputs in the tables.
      $("#form-ever tr td input").once('calcSummary').on('change', function (event) {
        let el = $(event.target); // The triggered input.
        let cell = el.parent().parent(); // The father cell of triggered element.
        let elValue = Number(el.val()); // The value of triggered input.
        let index = cell.index(); // The index of father cell of the input.
        // Calculate assuming quarter or year value.
        function calcTotal(periods) {
          return (periods.reduce((a, b) => a + b, 0) + 1) / periods.length;
        }
        // Get values of appropriate cells into array as numbers.
        function getValues() {
          let values = [];
          let current = cell;
          // Set count of the loop depends on quarter or month values is getting.
          let q = 3;
          if (index < 17) {
            q = 2;
          }
          // Walk across the appropriate cells and store values.
          for (let i = q; i >= 0; i--) {
            // If getting quarters walk every fours cell of the row within month list.
            if (index === 17) {
              let n = 16 - 4 * (i);
              current = $(cell.siblings()[n]);
            } else {
              // If getting month values just walk back to previous 3 cells.
              current = current.prev();
            }
            values[i] = Number($($(current.children()[0]).children()[0]).val());
          }
          return values;
        }
        // Compare calculated assuming value with value from a cell
        // and set value of the cell to calculated if it is out of the range.
        function checkAndSet(values) {
          let tmpTotal = calcTotal(values);
          if (Math.abs(tmpTotal - elValue) > 0.05) {
            el.val(tmpTotal.toFixed(2));
          }
        }
        // If a quarter input was triggered check its value and trigger the
        // the handler for the year input to update its value if necessary.
        if ((index % 4) === 0) {
          checkAndSet(getValues());
          $($($(cell.siblings()[16]).children()[0]).children()[0]).triggerHandler('change');
        } else if (index === 17) {
          // If the year input was triggered just checks its value.
          checkAndSet(getValues());
        } else {
          // If a month input was triggered trigger
          // the handler for the appropriate quarter input.
          let quarter = $(cell.siblings()[(((index / 4 >> 0) + 1) * 4) - 1]);
          $($(quarter.children()[0]).children()[0]).triggerHandler('change');
        }
      });
    }
  };
})(jQuery, Drupal);
