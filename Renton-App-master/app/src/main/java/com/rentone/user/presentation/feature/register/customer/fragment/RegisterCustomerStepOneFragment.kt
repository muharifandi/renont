package com.rentone.user.presentation.feature.register.customer.fragment
import android.os.Bundle
import android.util.Patterns
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.widget.doAfterTextChanged
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.rentone.user.R
import com.rentone.user.databinding.FragmentRegisterCustomerStepOneBinding
import com.rentone.user.presentation.feature.register.fragment.RegisterStepBaseFragment
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class RegisterCustomerStepOneFragment : RegisterStepBaseFragment() {

    private var _binding: FragmentRegisterCustomerStepOneBinding? = null
    private val binding get() = _binding!!

    private val viewModel: RegisterCustomerStepOneViewModel by viewModels()

    private var firstNameValid = false
    private var emailValid = false
    private var phoneValid = false
    private var passwordValid = false
    private var confirmPasswordValid = false

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentRegisterCustomerStepOneBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.inputFirstName.doAfterTextChanged { validateFirstName() }

        binding.checkLastname.check()

        binding.inputPhone.doAfterTextChanged { text ->
            val value = text.toString()
            when {
                value.isEmpty() -> {
                    viewModel.resetPhone()
                    binding.inputPhoneLayout.error = getString(R.string.phone_cannot_empty)
                    binding.checkPhone.uncheck()
                }
                !Patterns.PHONE.matcher(value).matches() -> {
                    viewModel.resetPhone()
                    binding.inputPhoneLayout.error = getString(R.string.phone_not_valid)
                    binding.checkPhone.uncheck()
                }
                else -> {
                    binding.inputPhoneLayout.error = getString(R.string.checking_phone)
                    binding.checkPhone.uncheck()
                    viewModel.checkPhone(value)
                }
            }
        }

        binding.inputEmail.doAfterTextChanged { text ->
            val value = text.toString()
            when {
                value.isEmpty() -> {
                    viewModel.resetEmail()
                    binding.inputEmailLayout.error = getString(R.string.email_cannot_empty)
                    binding.checkEmail.uncheck()
                }
                !Patterns.EMAIL_ADDRESS.matcher(value).matches() -> {
                    viewModel.resetEmail()
                    binding.inputEmailLayout.error = getString(R.string.email_not_valid)
                    binding.checkEmail.uncheck()
                }
                else -> {
                    binding.inputEmailLayout.error = getString(R.string.checking_email)
                    binding.checkEmail.uncheck()
                    viewModel.checkEmail(value)
                }
            }
        }

        binding.inputPassword.doAfterTextChanged { validatePassword() }
        binding.inputConfirmPassword.doAfterTextChanged { validateConfirmPassword() }

        observeState()
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.emailState.collect { state ->
                        when (state) {
                            is FieldCheckState.Valid -> {
                                emailValid = true
                                binding.inputEmailLayout.error = null
                                binding.checkEmail.check()
                            }
                            is FieldCheckState.Invalid -> {
                                emailValid = false
                                binding.inputEmailLayout.error = state.message
                                binding.checkEmail.uncheck()
                            }
                            else -> Unit
                        }
                    }
                }
                launch {
                    viewModel.phoneState.collect { state ->
                        when (state) {
                            is FieldCheckState.Valid -> {
                                phoneValid = true
                                binding.inputPhoneLayout.error = null
                                binding.checkPhone.check()
                            }
                            is FieldCheckState.Invalid -> {
                                phoneValid = false
                                binding.inputPhoneLayout.error = state.message
                                binding.checkPhone.uncheck()
                            }
                            else -> Unit
                        }
                    }
                }
            }
        }
    }

    private fun validateFirstName() {
        firstNameValid = binding.inputFirstName.text.toString().isNotEmpty()
        binding.inputFistNameLayout.error = if (firstNameValid) null else getString(R.string.first_name_cannot_empty)
        if (firstNameValid) binding.checkFirstname.check() else binding.checkFirstname.uncheck()
    }

    private fun validatePassword() {
        val password = binding.inputPassword.text.toString()
        val confirm = binding.inputConfirmPassword.text.toString()
        when {
            password.isEmpty() -> {
                passwordValid = false
                binding.inputPasswordLayout.error = getString(R.string.password_cannot_empty)
                binding.checkPassword.uncheck()
            }
            password.length < 8 -> {
                passwordValid = false
                binding.inputPasswordLayout.error = getString(R.string.password_minimum_character)
                binding.checkPassword.uncheck()
            }
            else -> {
                passwordValid = true
                binding.inputPasswordLayout.error = null
                binding.checkPassword.check()
            }
        }
        if (confirm.isNotEmpty()) validateConfirmPassword()
    }

    private fun validateConfirmPassword() {
        val password = binding.inputPassword.text.toString()
        val confirm = binding.inputConfirmPassword.text.toString()
        when {
            confirm.isEmpty() -> {
                confirmPasswordValid = false
                binding.inputConfirmPasswordLayout.error = getString(R.string.confirm_password_cannot_empty)
                binding.checkConfirmPassword.uncheck()
            }
            confirm != password -> {
                confirmPasswordValid = false
                binding.inputConfirmPasswordLayout.error = getString(R.string.confirm_password_not_match)
                binding.checkConfirmPassword.uncheck()
            }
            else -> {
                confirmPasswordValid = true
                binding.inputConfirmPasswordLayout.error = null
                binding.checkConfirmPassword.check()
            }
        }
    }

    override fun validateForm(): Boolean {
        validateFirstName()
        if (binding.inputPhone.text.toString().isEmpty()) {
            binding.inputPhoneLayout.error = getString(R.string.phone_cannot_empty)
            binding.checkPhone.uncheck()
        }
        if (binding.inputEmail.text.toString().isEmpty()) {
            binding.inputEmailLayout.error = getString(R.string.email_cannot_empty)
            binding.checkEmail.uncheck()
        }
        validatePassword()
        validateConfirmPassword()

        return firstNameValid && phoneValid && emailValid && passwordValid && confirmPasswordValid
    }

    override fun getFormValue(): Map<String, String> = mapOf(
        "first_name" to binding.inputFirstName.text.toString(),
        "last_name" to binding.inputLastName.text.toString(),
        "phone" to binding.inputPhone.text.toString(),
        "email" to binding.inputEmail.text.toString(),
        "password" to binding.inputPassword.text.toString(),
        "referal" to binding.inputReferal.text.toString()
    )

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
