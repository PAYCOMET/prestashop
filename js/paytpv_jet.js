function buildED() {
            var t = document.getElementById('expiry_date').value,
                n = t.substr(0, 2),
                a = t.substr(3, 2);
            $('[data-paytpv=\'dateMonth\']').val(n), $('[data-paytpv=\'dateYear\']').val(a)
        }

        (function() {
                (function() {
                    var $,
                    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

                    $ = jQuery;

        $.fn.validateCreditCard = function(callback, options) {
            var bind, card, card_type, card_types, get_card_type, is_valid_length, is_valid_luhn, normalize, validate, validate_number, _i, _len, _ref;
            card_types = [
          {
            name: 'amex',
            pattern: /^3[47]/,
            valid_length: [15]
          }, {
            name: 'diners_club_carte_blanche',
            pattern: /^30[0-5]/,
            valid_length: [14]
          }, {
            name: 'diners_club_international',
            pattern: /^36/,
            valid_length: [14]
          }, {
            name: 'jcb',
            pattern: /^35(2[89]|[3-8][0-9])/,
            valid_length: [16]
          }, {
            name: 'laser',
            pattern: /^(6304|670[69]|6771)/,
            valid_length: [16, 17, 18, 19]
          }, {
            name: 'visa_electron',
            pattern: /^(4026|417500|4508|4844|491(3|7))/,
            valid_length: [16]
          }, {
            name: 'visa',
            pattern: /^4/,
            valid_length: [16]
          }, {
            name: 'mastercard',
            // 20160603 2U7-GQS-M6X3 Cambiamos el patern ya que MC ha incluido nuevos rangos de bines
            pattern: /^(5[1-5]|222|2[3-6]|27[0-1]|2720)/,
            // 20160603 2U7-GQS-M6X3 Fin
            valid_length: [16]
          }, {
            name: 'maestro',
            pattern: /^(5018|5020|5038|6304|6759|676[1-3])/,
            valid_length: [12, 13, 14, 15, 16, 17, 18, 19]
          }, {
            name: 'discover',
            pattern: /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/,
            valid_length: [16]
          }
        ];
        bind = false;
        if (callback) {
          if (typeof callback === 'object') {
            options = callback;
            bind = false;
            callback = null;
          } else if (typeof callback === 'function') {
            bind = true;
          }
        }
        if (options === null) {
          options = {};
        }
        if (options.accept === null) {
          options.accept = (function() {
            var _i, _len, _results;
            _results = [];
            for (_i = 0, _len = card_types.length; _i < _len; _i++) {
              card = card_types[_i];
              _results.push(card.name);
            }
            return _results;
          })();
        }
        _ref = options.accept;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          card_type = _ref[_i];
          if (__indexOf.call((function() {
            var _j, _len1, _results;
            _results = [];
            for (_j = 0, _len1 = card_types.length; _j < _len1; _j++) {
              card = card_types[_j];
              _results.push(card.name);
            }
            return _results;
          })(), card_type) < 0) {
            throw 'Credit Card Not Valid';
          }
        }
        get_card_type = function(number) {
          var _j, _len1, _ref1;
          _ref1 = (function() {
            var _k, _len1, _ref1, _results;
            _results = [];
            for (_k = 0, _len1 = card_types.length; _k < _len1; _k++) {
              card = card_types[_k];
              if (_ref1 = card.name, __indexOf.call(options.accept, _ref1) >= 0) {
                _results.push(card);
              }
            }
            return _results;
          })();
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            card_type = _ref1[_j];
            if (typeof(number) !== 'undefined' && number.match(card_type.pattern)) {
              return card_type;
            }
          }
          return null;
        };
        is_valid_luhn = function(number) {
          var digit, n, sum, _j, _len1, _ref1;
          sum = 0;
          _ref1 = number.split('').reverse();
          for (n = _j = 0, _len1 = _ref1.length; _j < _len1; n = ++_j) {
            digit = _ref1[n];
            digit = +digit;
            if (n % 2) {
              digit *= 2;
              if (digit < 10) {
                sum += digit;
              } else {
                sum += digit - 9;
              }
            } else {
              sum += digit;
            }
          }
          return sum % 10 === 0;
        };
        is_valid_length = function(number, card_type) {
          var _ref1;
          return _ref1 = number.length, __indexOf.call(card_type.valid_length, _ref1) >= 0;
        };
        validate_number = (function(_this) {
          return function(number) {
            var length_valid, luhn_valid;
            card_type = get_card_type(number);
            luhn_valid = false;
            length_valid = false;
            if (card_type !== null) {
              luhn_valid = is_valid_luhn(number);
              length_valid = is_valid_length(number, card_type);
            }
            return {
              card_type: card_type,
              valid: luhn_valid && length_valid,
              luhn_valid: luhn_valid,
              length_valid: length_valid
            };
          };
        })(this);
        validate = (function(_this) {
          return function() {
            var number;
            number = normalize($(_this).val());
            return validate_number(number);
          };
        })(this);
        normalize = function(number) {
          if (typeof(number) !== 'undefined') {
            return number.replace(/[ -]/g, '');
          }
        };
        if (!bind) {
          return validate();
        }
        this.on('input.jccv', (function(_this) {
          return function() {
            $(_this).off('keyup.jccv');
            return callback.call(_this, validate());
          };
        })(this));
        this.on('keyup.jccv', (function(_this) {
          return function() {
            return callback.call(_this, validate());
          };
        })(this));
            callback.call(this, validate());
            return this;
          };

        }).call(this);
            $(function() {
                return $('[data-paytpv=\'paNumber\']').validateCreditCard(function(result) {
                $(this).removeClass().addClass('paytpv_merchant_pan');
                    if (result.card_type === null) {
                        return;
                    }
                    $(this).addClass(result.card_type.name);
                    if (result.valid) {
                        return $(this).addClass('valid');
                    } else {
                        return $(this).removeClass('valid');
                    }
                }, {
                accept: ['visa', 'visa_electron', 'mastercard', 'maestro', 'discover', 'amex']
                });
            });
        }).call(this);

        $(document).ready(function() {
            $('#expiry_date').on('input',function(){
                var curLength = $(this).val().length;
                if(curLength === 2){
                    var newInput = $(this).val();
                    newInput += '/';
                    $(this).val(newInput);
                }
            });
        })