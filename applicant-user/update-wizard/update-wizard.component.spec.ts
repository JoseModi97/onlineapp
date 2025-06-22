import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { UpdateWizardComponent } from './update-wizard.component';
import { CommonModule } from '@angular/common';

describe('UpdateWizardComponent', () => {
  let component: UpdateWizardComponent;
  let fixture: ComponentFixture<UpdateWizardComponent>;
  // let fb: FormBuilder; // Not strictly needed if component creates its own form

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ UpdateWizardComponent ],
      imports: [
        CommonModule,
        ReactiveFormsModule
      ],
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(UpdateWizardComponent);
    component = fixture.componentInstance;
    // fb = TestBed.inject(FormBuilder); // Component handles its form internally
    fixture.detectChanges(); // This will call ngOnInit
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize to step 1', () => {
    expect(component.currentStep).toBe(1);
  });

  it('should initialize wizardForm with three sections and required fields', () => {
    expect(component.wizardForm).toBeDefined();
    const section1 = component.wizardForm.get('section1') as FormGroup;
    const section2 = component.wizardForm.get('section2') as FormGroup;
    const section3 = component.wizardForm.get('section3') as FormGroup;

    expect(section1).toBeDefined();
    expect(section1.get('field1_1')).toBeDefined();
    expect(section1.get('field1_2')).toBeDefined();

    expect(section2).toBeDefined();
    expect(section2.get('field2_1')).toBeDefined();
    expect(section2.get('field2_2')).toBeDefined();

    expect(section3).toBeDefined();
    expect(section3.get('field3_1')).toBeDefined();
    expect(section3.get('field3_2')).toBeDefined();

    // Check initial validity (should be invalid due to required fields)
    expect(component.wizardForm.valid).toBeFalse();
    expect(section1.valid).toBeFalse();
  });

  describe('Navigation', () => {
    it('should not proceed to next step if current step is invalid', () => {
      component.currentStep = 1;
      // section1 is invalid by default
      component.nextStep();
      fixture.detectChanges();
      expect(component.currentStep).toBe(1);
    });

    it('should proceed to next step if current step is valid', () => {
      component.currentStep = 1;
      component.wizardForm.get('section1').setValue({ field1_1: 'test', field1_2: 'test' });
      fixture.detectChanges();
      expect(component.wizardForm.get('section1').valid).toBeTrue();

      component.nextStep();
      fixture.detectChanges();
      expect(component.currentStep).toBe(2);
    });

    it('should not go to next step if on the last step (step 3)', () => {
      component.currentStep = 3;
      component.wizardForm.get('section3').setValue({ field3_1: 'test', field3_2: 'test' });
      fixture.detectChanges();
      expect(component.wizardForm.get('section3').valid).toBeTrue();

      component.nextStep(); // Should do nothing as it's the last step
      fixture.detectChanges();
      expect(component.currentStep).toBe(3);
    });

    it('should go to previous step', () => {
      component.currentStep = 2;
      component.previousStep();
      fixture.detectChanges();
      expect(component.currentStep).toBe(1);
    });

    it('should not go to previous step if on the first step (step 1)', () => {
      component.currentStep = 1;
      component.previousStep();
      fixture.detectChanges();
      expect(component.currentStep).toBe(1);
    });
  });

  describe('Validation and Form State', () => {
    it('currentSectionForm should return the correct form group for step 1', () => {
      component.currentStep = 1;
      expect(component.currentSectionForm).toBe(component.wizardForm.get('section1'));
    });

    it('currentSectionForm should return the correct form group for step 2', () => {
      component.currentStep = 2;
      expect(component.currentSectionForm).toBe(component.wizardForm.get('section2'));
    });

    it('currentSectionForm should return the correct form group for step 3', () => {
      component.currentStep = 3;
      expect(component.currentSectionForm).toBe(component.wizardForm.get('section3'));
    });

    it('isStepValid should correctly report validity of a step', () => {
      expect(component.isStepValid(1)).toBeFalse(); // Initially invalid
      component.wizardForm.get('section1').setValue({ field1_1: 'test', field1_2: 'test' });
      expect(component.isStepValid(1)).toBeTrue();
    });

    it('nextStep() should mark fields as touched if current section is invalid', () => {
        component.currentStep = 1;
        const section1Field1 = component.wizardForm.get('section1.field1_1');

        expect(section1Field1.touched).toBeFalse();
        component.nextStep(); // section1 is invalid
        expect(section1Field1.touched).toBeTrue();
        expect(component.wizardForm.get('section1.field1_2').touched).toBeTrue();
    });
  });

  describe('Button States (via helper methods)', () => {
    // The isNextDisabled() was removed in favor of direct check in template,
    // but the logic for disabling next is `currentSectionForm.invalid || currentStep === 3`
    // We'll test the parts that control this.

    it('Next button logic: should be enabled if current section is valid and not last step', () => {
        component.currentStep = 1;
        component.wizardForm.get('section1').setValue({ field1_1: 'test', field1_2: 'test' });
        fixture.detectChanges(); // update form state
        // Template logic for next button: *ngIf="currentStep < 3" [disabled]="currentSectionForm?.invalid"
        expect(component.currentStep < 3).toBeTrue();
        expect(component.currentSectionForm.invalid).toBeFalse();
        // So, effectively, it should not be disabled.
    });

    it('Next button logic: should be disabled if current section is invalid', () => {
        component.currentStep = 1;
        component.wizardForm.get('section1').get('field1_1').setValue(''); // make it invalid
        fixture.detectChanges();
        expect(component.currentSectionForm.invalid).toBeTrue();
        // So, effectively, it should be disabled.
    });

    it('Next button logic: should not be shown if on last step (step 3)', () => {
        component.currentStep = 3;
        fixture.detectChanges();
        // Template logic for next button: *ngIf="currentStep < 3"
        // This means the button itself would be hidden by *ngIf.
        // We are testing the conditions that lead to this.
        expect(component.currentStep < 3).toBeFalse();
    });

    it('Submit button logic: should be enabled if section 3 is valid', () => {
        component.currentStep = 3;
        component.wizardForm.get('section3').setValue({ field3_1: 'test', field3_2: 'test' });
        fixture.detectChanges();
        // Template logic for submit: *ngIf="currentStep === 3" [disabled]="wizardForm.get('section3').invalid"
        expect(component.wizardForm.get('section3').invalid).toBeFalse();
    });

    it('Submit button logic: should be disabled if section 3 is invalid', () => {
        component.currentStep = 3;
        component.wizardForm.get('section3').get('field3_1').setValue(''); // make it invalid
        fixture.detectChanges();
        expect(component.wizardForm.get('section3').invalid).toBeTrue();
    });
  });

  describe('onSubmit', () => {
    it('onSubmit should not proceed if form is invalid', () => {
      spyOn(console, 'log');
      spyOn(console, 'error');
      component.wizardForm.get('section1').setValue({ field1_1: 'test', field1_2: 'test' });
      component.wizardForm.get('section2').setValue({ field2_1: 'test', field2_2: 'test' });
      component.wizardForm.get('section3').setValue({ field3_1: '', field3_2: '' }); // section 3 invalid
      fixture.detectChanges();

      component.onSubmit();
      expect(component.wizardForm.valid).toBeFalse();
      expect(console.log).not.toHaveBeenCalledWith('Form Submitted!', jasmine.any(Object));
      expect(console.error).toHaveBeenCalledWith('Form is invalid. Cannot submit.');
    });

    it('onSubmit should proceed if form is valid', () => {
      spyOn(console, 'log');
      spyOn(window, 'alert'); // Spy on window.alert
      component.wizardForm.get('section1').setValue({ field1_1: 'test', field1_2: 'test' });
      component.wizardForm.get('section2').setValue({ field2_1: 'test', field2_2: 'test' });
      component.wizardForm.get('section3').setValue({ field3_1: 'test', field3_2: 'test' });
      fixture.detectChanges();

      expect(component.wizardForm.valid).toBeTrue();
      component.onSubmit();
      expect(console.log).toHaveBeenCalledWith('Form Submitted!', component.wizardForm.value);
      expect(window.alert).toHaveBeenCalledWith('Form submitted successfully!');
    });
  });

});
