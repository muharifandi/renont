package com.rentone.user.presentation.feature.partner.changecompanyname
import android.app.Activity
import android.os.Bundle
import android.view.MenuItem
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
import com.rentone.user.databinding.ActivityPartnerChangeCompanyNameBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class PartnerChangeCompanyNameActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerChangeCompanyNameBinding
    private val viewModel: PartnerChangeCompanyNameViewModel by viewModels()

    private var companyNameValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerChangeCompanyNameBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        val companyName = intent.getStringExtra("company_name")
        if (!companyName.isNullOrEmpty()) {
            binding.checkCompanyName.check()
            companyNameValid = true
        }
        binding.inputCompanyName.setText(companyName)
        binding.inputCompanyName.doAfterTextChanged { validateCompanyName() }

        binding.btnChangeCompanyName.setOnClickListener {
            if (validateForm()) {
                viewModel.changeCompanyName(binding.inputCompanyName.text.toString())
            } else {
                android.widget.Toast.makeText(this, R.string.check_form_again, android.widget.Toast.LENGTH_LONG).show()
            }
        }

        observeState()
    }

    private fun validateCompanyName() {
        if (binding.inputCompanyName.text.isNullOrBlank()) {
            companyNameValid = false
            binding.inputCompanyNameLayout.error = getString(R.string.company_name_cannot_empty)
            binding.checkCompanyName.uncheck()
        } else {
            companyNameValid = true
            binding.inputCompanyNameLayout.error = null
            binding.checkCompanyName.check()
        }
    }

    private fun validateForm(): Boolean {
        validateCompanyName()
        return companyNameValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.state.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<OperationResult>) {
        binding.btnChangeCompanyName.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.change_company_name))
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
                    .setTitle(getString(R.string.change_company_name))
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
