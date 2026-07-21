package com.nusatim.sapiriku.presentation.feature.account.changepassword
import android.app.Activity
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.widget.doAfterTextChanged
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityCustomerChangePasswordBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerChangePasswordActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerChangePasswordBinding
    private val viewModel: CustomerChangePasswordViewModel by viewModels()

    private var oldPasswordValid = false
    private var newPasswordValid = false
    private var confirmNewPasswordValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerChangePasswordBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.inputOldPassword.doAfterTextChanged { validateOldPassword() }
        binding.inputNewPassword.doAfterTextChanged { validateNewPassword() }
        binding.inputConfirmNewPassword.doAfterTextChanged { validateConfirmNewPassword() }

        binding.btnChangePassword.setOnClickListener {
            if (validateForm()) {
                viewModel.changePassword(binding.inputOldPassword.text.toString(), binding.inputNewPassword.text.toString())
            } else {
                Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
            }
        }

        observeState()
    }

    private fun validateOldPassword() {
        val text = binding.inputOldPassword.text.toString()
        when {
            text.isEmpty() -> {
                oldPasswordValid = false
                binding.inputOldPasswordLayout.error = getString(R.string.password_cannot_empty)
                binding.checkOldPassword.uncheck()
            }
            text.length < 8 -> {
                oldPasswordValid = false
                binding.inputOldPasswordLayout.error = getString(R.string.password_minimum_character)
                binding.checkOldPassword.uncheck()
            }
            else -> {
                oldPasswordValid = true
                binding.inputOldPasswordLayout.error = null
                binding.checkOldPassword.check()
            }
        }
    }

    private fun validateNewPassword() {
        val newText = binding.inputNewPassword.text.toString()
        val confirmText = binding.inputConfirmNewPassword.text.toString()
        when {
            newText.isEmpty() -> {
                newPasswordValid = false
                binding.inputNewPasswordLayout.error = getString(R.string.password_cannot_empty)
                binding.checkNewPassword.uncheck()
            }
            newText.length < 8 -> {
                newPasswordValid = false
                binding.inputNewPasswordLayout.error = getString(R.string.password_minimum_character)
                binding.checkNewPassword.uncheck()
            }
            confirmText != newText -> {
                confirmNewPasswordValid = false
                binding.inputConfirmNewPasswordLayout.error = getString(R.string.confirm_password_not_match)
                binding.checkConfirmNewPassword.uncheck()

                newPasswordValid = true
                binding.inputNewPasswordLayout.error = null
                binding.checkNewPassword.check()
            }
            else -> {
                confirmNewPasswordValid = true
                binding.inputConfirmNewPasswordLayout.error = null
                binding.checkConfirmNewPassword.check()

                newPasswordValid = true
                binding.inputNewPasswordLayout.error = null
                binding.checkNewPassword.check()
            }
        }
    }

    private fun validateConfirmNewPassword() {
        val confirmText = binding.inputConfirmNewPassword.text.toString()
        val newText = binding.inputNewPassword.text.toString()
        when {
            confirmText.isEmpty() -> {
                confirmNewPasswordValid = false
                binding.inputConfirmNewPasswordLayout.error = getString(R.string.confirm_password_cannot_empty)
                binding.checkConfirmNewPassword.uncheck()
            }
            confirmText != newText -> {
                confirmNewPasswordValid = false
                binding.inputConfirmNewPasswordLayout.error = getString(R.string.confirm_password_not_match)
                binding.checkConfirmNewPassword.uncheck()
            }
            else -> {
                confirmNewPasswordValid = true
                binding.inputConfirmNewPasswordLayout.error = null
                binding.checkConfirmNewPassword.check()
            }
        }
    }

    private fun validateForm(): Boolean {
        validateOldPassword()
        validateNewPassword()
        validateConfirmNewPassword()
        return oldPasswordValid && newPasswordValid && confirmNewPasswordValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.state.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<OperationResult>) {
        binding.btnChangePassword.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.change_password))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ ->
                        if (state.data.success) {
                            setResult(Activity.RESULT_OK)
                            finish()
                        }
                    }
                    .show()
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.change_password))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
            }
            else -> Unit
        }
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }
}
