import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-update-wizard',
  templateUrl: './update-wizard.component.html',
  styleUrls: ['./update-wizard.component.scss']
})
export class UpdateWizardComponent implements OnInit {
  wizardForm: FormGroup;
  currentStep = 1;

  constructor(private fb: FormBuilder) { }

  ngOnInit(): void {
    this.wizardForm = this.fb.group({
      section1: this.fb.group({
        field1_1: ['', Validators.required],
        field1_2: ['', Validators.required]
      }),
      section2: this.fb.group({
        field2_1: ['', Validators.required],
        field2_2: ['', Validators.required]
      }),
      section3: this.fb.group({
        field3_1: ['', Validators.required],
        field3_2: ['', Validators.required]
      })
    });
  }

  get currentSectionForm(): FormGroup {
    if (this.currentStep === 1) {
      return this.wizardForm.get('section1') as FormGroup;
    } else if (this.currentStep === 2) {
      return this.wizardForm.get('section2') as FormGroup;
    } else if (this.currentStep === 3) {
      return this.wizardForm.get('section3') as FormGroup;
    }
    return null;
  }

  nextStep(): void {
    if (this.currentSectionForm.valid) {
      if (this.currentStep < 3) {
        this.currentStep++;
      }
    } else {
      // Mark all fields as touched to display validation errors
      this.currentSectionForm.markAllAsTouched();
    }
  }

  previousStep(): void {
    if (this.currentStep > 1) {
      this.currentStep--;
    }
  }

  isStepActive(step: number): boolean {
    return this.currentStep === step;
  }

  // Helper to check if a specific step is valid.
  // This can be used for UI indicators if needed.
  isStepValid(step: number): boolean {
    if (step === 1) {
      return this.wizardForm.get('section1').valid;
    } else if (step === 2) {
      return this.wizardForm.get('section2').valid;
    } else if (step === 3) {
      return this.wizardForm.get('section3').valid;
    }
    return false;
  }

  // Used in the template to disable the "Next" button
  isNextDisabled(): boolean {
    if (!this.currentSectionForm) { // Should not happen if currentStep is always 1, 2, or 3
        return true;
    }
    // Disable if current section is invalid OR if it's the last step (no "Next" from step 3)
    return this.currentSectionForm.invalid || this.currentStep === 3;
  }

  onSubmit(): void {
    if (this.wizardForm.valid) {
      console.log('Form Submitted!', this.wizardForm.value);
      // Here you would typically send the data to a server
      alert('Form submitted successfully!');
    } else {
      console.error('Form is invalid. Cannot submit.');
      // Optionally, mark all fields in all sections as touched if submitting from a global submit button
      // For this wizard, submission only happens at the last step,
      // and the last step's "Submit" button is only enabled if that section is valid.
      // The overall form validity check ([disabled]="wizardForm.invalid") on the submit button handles this.
    }
  }
}
