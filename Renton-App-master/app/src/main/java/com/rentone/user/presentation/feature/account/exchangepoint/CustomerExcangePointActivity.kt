package com.rentone.user.presentation.feature.account.exchangepoint
import android.app.Activity
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.rentone.user.R
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.ExchangePointConfig
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityCustomerExcangePointBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerExcangePointActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerExcangePointBinding
    private val viewModel: CustomerExcangePointViewModel by viewModels()

    private var nominalValid = false
    private var config: ExchangePointConfig? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerExcangePointBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        observeState()
        viewModel.loadConfig()
    }

    private fun setupForm(config: ExchangePointConfig) {
        this.config = config

        val watcher = object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputNominal, s)
                validateNominal()
            }
        }
        binding.inputNominal.addTextChangedListener(watcher)

        binding.txtRateExchange.text = "1 Point = Rp. ${ViewUtils.formatCurrency(config.ratePointToBalance)},-"
        binding.btnExchange.setOnClickListener {
            if (validateForm()) {
                viewModel.exchangePoint(binding.inputNominal.text.toString().replace(",", ""))
            } else {
                Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
            }
        }
    }

    private fun validateNominal() {
        val config = this.config ?: return
        val text = binding.inputNominal.text.toString()
        if (text.isNotEmpty()) {
            val nominal = text.replace(",", "").toDoubleOrNull() ?: 0.0
            if (nominal < config.exchangePointMinimum) {
                nominalValid = false
                binding.inputNominalLayout.error =
                    getString(R.string.nominal_minimum_message, ViewUtils.formatCurrency(config.exchangePointMinimum.toDouble()))
                binding.checkNominal.uncheck()
            } else {
                nominalValid = true
                binding.inputNominalLayout.error = null
                binding.checkNominal.check()
            }
        } else {
            nominalValid = false
            binding.inputNominalLayout.error = getString(R.string.nominal_cannot_empty)
            binding.checkNominal.uncheck()
        }
    }

    private fun validateForm(): Boolean {
        validateNominal()
        return nominalValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.config.collect { state -> handleConfigState(state) }
                }
                launch {
                    viewModel.exchangeState.collect { state -> handleExchangeState(state) }
                }
            }
        }
    }

    private fun handleConfigState(state: UiState<ExchangePointConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_exchange_point))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun handleExchangeState(state: UiState<OperationResult>) {
        binding.btnExchange.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_exchange_point))
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
                    .setTitle(getString(R.string.post_exchange_point))
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
