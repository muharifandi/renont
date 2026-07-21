package com.rentone.user.presentation.feature.account.changename
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
import com.rentone.user.R
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.ActivityCustomerChangeNameBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerChangeNameActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerChangeNameBinding
    private val viewModel: CustomerChangeNameViewModel by viewModels()

    private var firstNameValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerChangeNameBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.checkFirstName.check()
        firstNameValid = true
        binding.inputFirstName.setText(intent.getStringExtra("first_name"))
        binding.inputFirstName.doAfterTextChanged { validateFirstName() }

        binding.checkLastName.check()
        binding.inputLastName.setText(intent.getStringExtra("last_name"))

        binding.btnChangeName.setOnClickListener {
            if (validateForm()) {
                viewModel.changeName(binding.inputFirstName.text.toString(), binding.inputLastName.text.toString())
            } else {
                Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
            }
        }

        observeState()
    }

    private fun validateFirstName() {
        if (binding.inputFirstName.text.isNullOrBlank()) {
            firstNameValid = false
            binding.inputFirstNameLayout.error = getString(R.string.first_name_cannot_empty)
            binding.checkFirstName.uncheck()
        } else {
            firstNameValid = true
            binding.inputFirstNameLayout.error = null
            binding.checkFirstName.check()
        }
    }

    private fun validateForm(): Boolean {
        validateFirstName()
        return firstNameValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.state.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<OperationResult>) {
        binding.btnChangeName.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.change_name))
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
                    .setTitle(getString(R.string.change_name))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
            }
            else -> Unit
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
