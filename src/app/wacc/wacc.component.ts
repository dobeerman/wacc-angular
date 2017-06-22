import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-wacc',
  templateUrl: './wacc.component.html',
  styleUrls: ['./wacc.component.css']
})

export class WaccComponent implements OnInit {
  form: FormGroup;
  formErrors = { e:'', re:'', d:'', rd:'' };
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
    }
  };

  constructor(private fb: FormBuilder) {}

  ngOnInit() {
    this.buildForm();
  }

  /**
   * build the initial forms
   */
  buildForm() {
    // build our form
    this.form = this.fb.group({
      e: [0],
      re: [0, [Validators.min(0), Validators.max(100)]],
      d: [0],
      rd: [0, [Validators.min(0), Validators.max(100)]],
      t: [20]
    });

    // watch for changes and validate
    this.form.valueChanges.subscribe(data => this.validateForm());
  }
  
  /**
   * validate the entire form
   */
  validateForm() {
    for (let field in this.formErrors) {
      // clear that input field errors
      this.formErrors[field] = '';

      // grab and input field by name
      let input = this.form.get(field);

      if (input.invalid && input.dirty) {
        // figure out the type of error
        //loop over the formErrors field names
        for (let error in input.errors)
        // assign that type of error message to a variable
        this.formErrors[field] = this.validationMessages[field][error];
      }
    }
  }

  get waccCalc() {
    let e = this.form.value.e * 1;
    let re = this.form.value.re / 100;
    let d = this.form.value.d * 1;
    let rd = this.form.value.rd / 100;
    let t = 1 - this.form.value.t / 100;

    let v = e + d;

    let output = '';
    if (v) { // prevent divide by zero
      output = "WACC: " + (( (e / v) * re + (d / v) * rd * t ) * 100).toFixed(2) + " %";
    } else {
      output = '';
    }
    
    // console.log(this.formErrors);
    return output;
  }
}
