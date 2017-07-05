import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-wacc',
  templateUrl: './wacc.component.html',
  styleUrls: ['./wacc.component.css']
})

export class WaccComponent implements OnInit {
  form: FormGroup;
  formErrors = { e: '', re: '', d: '', rd: '', t: '' };
  validationMessages = {
    e: { 'required': 'Equity is required.' },
    re: {
      'required': 'Cost is required',
      'min': 'Must be more than 0.',
      'max': 'Must be less than 100.',
    },
    d: { 'required': 'Debt is required.' },
    rd: {
      'required': 'Cost is required',
      'min': 'Must be more than 0.',
      'max': 'Must be less than 100.',
    },
    t: {}
  };

  constructor(private fb: FormBuilder) { }

  ngOnInit() {
    this.buildForm();
  }

  /**
   * build the initial forms
   */
  buildForm() {
    // build our form
    this.form = this.fb.group({
      e: [null],
      re: [null, [Validators.min(0), Validators.max(100)]],
      d: [null],
      rd: [null, [Validators.min(0), Validators.max(100)]],
      t: [20]
    });

    // watch for changes and validate
    this.form.valueChanges.subscribe(data => this.validateForm());
  }

  /**
   * validate the entire form
   */
  validateForm() {
    for (const field in this.formErrors) {
      if (this.formErrors.hasOwnProperty(field)) {
        // clear that input field errors
        this.formErrors[field] = '';

        // grab and input field by name
        const input = this.form.get(field);

        if (input.invalid && input.dirty) {
          // figure out the type of error
          // loop over the formErrors field names
          for (const error in input.errors) {
            if (input.errors.hasOwnProperty(error)) {
              // assign that type of error message to a variable
              this.formErrors[field] = this.validationMessages[field][error];
            }
          }
        }
      }
    }
  }

  get waccCalc() {
    const e = this.form.value.e * 1;
    const re = this.form.value.re / 100;
    const d = this.form.value.d * 1;
    const rd = this.form.value.rd / 100;
    const t = 1 - this.form.value.t / 100;

    const v = e + d;

    let output = '';
    if (v) { // prevent divide by zero
      output = 'WACC: ' + (((e / v) * re + (d / v) * rd * t) * 100).toFixed(2) + ' %';
    } else {
      output = '';
    }

    // console.log(this.formErrors);
    return output;
  }
}
