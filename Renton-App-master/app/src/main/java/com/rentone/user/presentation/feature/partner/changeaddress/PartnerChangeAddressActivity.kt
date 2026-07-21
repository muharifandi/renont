package com.rentone.user.presentation.feature.partner.changeaddress
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
import com.rentone.user.databinding.ActivityPartnerChangeAddressBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class PartnerChangeAddressActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerChangeAddressBinding
    private val viewModel: PartnerChangeAddressViewModel by viewModels()

    private var addressValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerChangeAddressBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.inputAddress.setText(intent.getStringExtra("address"))
        binding.inputAddress.doAfterTextChanged { validateAddress() }

        binding.btnChangeAddress.setOnClickListener {
            if (validateForm()) {
                viewModel.changeAddress(binding.inputAddress.text.toString())
            } else {
                android.widget.Toast.makeText(this, R.string.check_form_again, android.widget.Toast.LENGTH_LONG).show()
            }
        }

        observeState()
    }

    private fun validateAddress() {
        if (binding.inputAddress.text.isNullOrBlank()) {
            addressValid = false
            binding.inputAddressLayout.error = getString(R.string.address_cannot_empty)
            binding.checkAddress.uncheck()
        } else {
            addressValid = true
            binding.inputAddressLayout.error = null
            binding.checkAddress.check()
        }
    }

    private fun validateForm(): Boolean {
        validateAddress()
        return addressValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.state.collect { state -> handleUiState(state) }
            }
        }
    }

    private fun handleUiState(state: UiState<OperationResult>) {
        binding.btnChangeAddress.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.change_address))
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
                    .setTitle(getString(R.string.change_address))
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
