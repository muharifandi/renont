package com.rentone.user.presentation.feature.account.requestwithdraw
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
import com.rentone.user.domain.model.RequestWithdrawConfig
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.MenuUtils
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityCustomerRequestWithdrawBinding
import com.rentone.user.domain.model.CustomerBank
import com.rentone.user.presentation.feature.account.adapter.ListCustomerBankAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerRequestWithdrawActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerRequestWithdrawBinding
    private val viewModel: CustomerRequestWithdrawViewModel by viewModels()

    private var inputBankId = -1
    private var bankValid = false
    private var nominalValid = false
    private var config: RequestWithdrawConfig? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerRequestWithdrawBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        observeState()
        viewModel.loadConfig()
    }

    private fun setupForm(config: RequestWithdrawConfig) {
        this.config = config

        if (config.banks.isNotEmpty()) {
            inputBankId = config.banks[0].id
            binding.inputBank.setText(config.banks[0].toString())
            bankValid = true
            binding.checkBank.check()
        }

        binding.inputBank.setOnClickListener {
            val adapter = ListCustomerBankAdapter(this, ArrayList(config.banks))
            MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
                val data: CustomerBank = adapter.getItem(which)!!
                inputBankId = data.id
                binding.inputBank.setText(data.toString())
                binding.checkBank.check()
                bankValid = true
            })
        }

        val watcher = object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputNominal, s)
                validateNominal()
            }
        }
        binding.inputNominal.addTextChangedListener(watcher)

        binding.btnWIthdraw.setOnClickListener {
            if (validateForm()) {
                viewModel.requestWithdraw(inputBankId, binding.inputNominal.text.toString().replace(",", ""))
            } else {
                Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
            }
        }
    }

    private fun validateBank() {
        if (inputBankId == -1) {
            bankValid = false
            binding.inputBankLayout.error = getString(R.string.bank_cannot_empty)
            binding.checkBank.uncheck()
        } else {
            bankValid = true
            binding.inputBankLayout.error = null
            binding.checkBank.check()
        }
    }

    private fun validateNominal() {
        val config = this.config ?: return
        val text = binding.inputNominal.text.toString()
        if (text.isNotEmpty()) {
            val nominal = text.replace(",", "").toDoubleOrNull() ?: 0.0
            val multipleCheck = nominal % 1000.0
            when {
                nominal < config.withdrawMinimum -> {
                    nominalValid = false
                    binding.inputNominalLayout.error = getString(
                        R.string.withdraw_minimum_message,
                        "Rp.${ViewUtils.formatCurrency(config.withdrawMinimum)},-"
                    )
                    binding.checkNominal.uncheck()
                }
                multipleCheck > 0.0 -> {
                    nominalValid = false
                    binding.inputNominalLayout.error = getString(R.string.nominal_must_multiple_thousand)
                    binding.checkNominal.uncheck()
                }
                else -> {
                    nominalValid = true
                    binding.inputNominalLayout.error = null
                    binding.checkNominal.check()
                }
            }
        } else {
            nominalValid = false
            binding.inputNominalLayout.error = getString(R.string.nominal_cannot_empty)
            binding.checkNominal.uncheck()
        }
    }

    private fun validateForm(): Boolean {
        validateBank()
        validateNominal()
        return bankValid && nominalValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.requestState.collect { state -> handleRequestState(state) } }
            }
        }
    }

    private fun handleConfigState(state: UiState<RequestWithdrawConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_withdraw))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun handleRequestState(state: UiState<OperationResult>) {
        binding.btnWIthdraw.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_withdraw))
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
                    .setTitle(getString(R.string.post_withdraw))
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
