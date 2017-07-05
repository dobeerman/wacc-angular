import { Component, OnInit, Input } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { AngularFireDatabase, FirebaseListObservable, FirebaseObjectObservable } from 'angularfire2/database';
// import { WaccResult } from './waccresult';

@Component({
  selector: 'app-waccpro',
  templateUrl: './waccpro.component.html',
  styleUrls: ['./waccpro.component.css'],
})

export class WaccproComponent implements OnInit {
  WaccUSD: string;
  WaccRUB: string;
  rForm: FormGroup;

  /**
   * Form's fields definitions
   */
  industry: Array<any> = ['0'];
  shareOfEquity: number;
  totalRevenue: number;
  costOfDebtCheckbox: boolean;
  costOfDebtInputbox: number;
  baseOfDebtCalc: 'avrg'; // avrg or spread
  ebit: number;
  interest: number;
  betaCorrect: boolean;

  // items: FirebaseListObservable<any>;
  betaemerg: FirebaseObjectObservable<any>;
  selected: string;
  items: FirebaseObjectObservable<any>;

  // Fill initial data
  cboe: number;
  erp: number;
  usd: number;
  caps: any;
  loans: any;
  rusbonds: number;
  spread: any;
  tax: number;
  yield: number;

  // Init result var
  // waccResult: Object = { USD: 0, RUB: 0 };

  titleAlert: 'This field is required';

  constructor(private fb: FormBuilder, db: AngularFireDatabase) {
    // this.betaemerg = db.object('data/betaemerg', { preserveSnapshot: true });
    // console.log(this.betaemerg);
    this.items = db.object('/data', { preserveSnapshot: true });
    // make a JSON object
    this.items.subscribe(snapshot => {

      this.betaemerg = snapshot.child('betaemerg').val();
      this.selected = this.betaemerg[0];

      this.cboe = snapshot.child('CBOE/bsd').val();
      this.erp = snapshot.child('ERP').val();
      this.usd = snapshot.child('USD/Vcurs').val();
      this.caps = snapshot.child('caps').val();
      this.loans = snapshot.child('loans').val();
      this.rusbonds = snapshot.child('rusbonds').val();
      this.spread = snapshot.child('spread').val();
      this.tax = snapshot.child('tax').val();
      this.yield = snapshot.child('yield').val();
    });

    this.rForm = fb.group({
      industry: [null, Validators.required],
      shareOfEquity: ['', Validators.compose([
        Validators.required,
        Validators.min(0),
        Validators.max(100)
      ])],
      totalRevenue: [null, Validators.compose([
        Validators.required,
        Validators.min(0)
      ])],
      costOfDebtCheckbox: [''],
      costOfDebtInputbox: [null],
      baseOfDebtCalc: ['avrg'],
      ebit: [null],
      interest: [null],
      betaCorrect: [false]
    })
  }

  ngOnInit() {
    this.rForm.get('costOfDebtCheckbox').valueChanges.subscribe(
      (costOfDebtCheckbox) => {
        if (costOfDebtCheckbox) {
          this.rForm.get('costOfDebtInputbox').setValidators([
            Validators.required,
            Validators.min(0),
            Validators.max(100)
          ])
        } else {
          this.rForm.get('costOfDebtInputbox').setValidators(Validators.nullValidator);
        }
        this.rForm.get('costOfDebtInputbox').updateValueAndValidity();
      }
    )
  }

  get WaccResult() {

    if (!this.yield || !this.rForm.value.industry) {

      return [
        { key: 'USD', val: 0 },
        { key: 'RUB', val: 0 }
      ];

    } else {

      const industry = this.rForm.value.industry * 1;
      const shareofequity = this.rForm.value.shareOfEquity / 100;
      const shareofdebt = 1 - shareofequity * 1;
      const trUSD = this.rForm.value.totalRevenue / this.usd;

      const ebit = this.rForm.value.ebit * 1;
      const interest = this.rForm.value.interest * 1;

      const iknow = this.rForm.value.costOfDebtCheckbox;
      const costofdebt = this.rForm.value.costOfDebtInputbox * 1;

      const betacorrect = this.rForm.value.betaCorrect;

      const beta_c = shareofequity ? industry * (1 + shareofdebt / shareofequity * (1 - this.tax)) : 1; // Company's beta
      const base = this.rForm.value.baseOfDebtCalc;

      const countryRisk = this.rusbonds / 100 - this.cboe / 100;
      const capBonus = this.findValue(trUSD, this.caps, 'bonus');
      const spreadRet = interest > 0 ? this.findValue(ebit / interest, trUSD > 5000 ? this.spread.above : this.spread.below, 'spread') : 0;

      let costOfEquityUSD = 0;
      if (betacorrect) {
        costOfEquityUSD = this.cboe / 100 + beta_c * (this.erp + countryRisk) + capBonus;
      } else {
        costOfEquityUSD = this.cboe / 100 + beta_c * this.erp + countryRisk + capBonus;
      }

      const costOfEquityRUB = this.yield / 100 + beta_c * this.erp + capBonus;

      let costOfDebtUSD = 0;
      let costOfDebtRUB = 0;
      if (iknow) {
        costOfDebtUSD = costofdebt / 100;
        costOfDebtRUB = costofdebt / 100;
      } else if (base === 'spread') {
        costOfDebtUSD = this.rusbonds / 100 + spreadRet;
        costOfDebtRUB = this.yield / 100 + spreadRet;
      } else {
        costOfDebtUSD = (trUSD < 1000 ? this.loans.USD.msp : this.loans.USD.regular) / 100;
        costOfDebtRUB = (trUSD < 1000 ? this.loans.RUB.msp : this.loans.RUB.regular) / 100;
      }

      const test = {
        costOfEquityUSD: costOfEquityUSD,
        costOfDebtUSD: costOfDebtUSD,
        capBonus: capBonus,
        trUSD: trUSD
      }

      this.WaccUSD = ((costOfEquityUSD * shareofequity + costOfDebtUSD * shareofdebt * (1 - this.tax)) * 100).toFixed(2);
      this.WaccRUB = ((costOfEquityRUB * shareofequity + costOfDebtRUB * shareofdebt * (1 - this.tax)) * 100).toFixed(2);

    }

    return [
      {
        key: 'USD',
        val: this.WaccUSD
      },
      {
        key: 'RUB',
        val: this.WaccRUB
      }
    ];
  }

  findValue(value, values, ret) {
    const precision = 0.00001;
    let def = 0;

    for (const item in values) {

      if ((value - values[item]['min']) > precision && (value - values[item]['max']) <= precision) {
        def = values[item][ret];
        break;
      }
    }

    return def;
  }

}
