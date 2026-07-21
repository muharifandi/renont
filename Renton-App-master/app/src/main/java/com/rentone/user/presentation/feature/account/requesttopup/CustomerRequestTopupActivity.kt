package com.rentone.user.presentation.feature.account.requesttopup
import android.content.Intent
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
import com.rentone.user.domain.model.RequestTopupConfig
import com.rentone.user.domain.model.TopupRequestResult
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.MenuUtils
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityCustomerRequestTopupBinding
import com.rentone.user.domain.model.CompanyBank
import com.rentone.user.presentation.feature.account.requesttopup.adapter.ListCompanyBankAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.account.verificationtopup.CustomerVerificationTopupActivity

@AndroidEntryPoint
class CustomerRequestTopupActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerRequestTopupBinding
    private val viewModel: CustomerRequestTopupViewModel by viewModels()

    private var inputBankId = -1
    private var nominalValid = false
    private var config: RequestTopupConfig? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerRequestTopupBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        observeState()
        viewModel.loadConfig()
    }

    private fun setupForm(config: RequestTopupConfig) {
        this.config = config
        binding.checkBank.check()

        if (config.banks.isNotEmpty()) {
            inputBankId = config.banks[0].id
            binding.inputBank.setText(config.banks[0].toString())
        }
        binding.inputBank.setOnClickListener {
            val adapter = ListCompanyBankAdapter(this, ArrayList(config.banks))
            MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
                val data: CompanyBank = adapter.getItem(which)!!
                inputBankId = data.id
                binding.inputBank.setText(data.toString())
                binding.checkBank.check()
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

        binding.btnTopup.setOnClickListener {
            if (validateForm()) {
                viewModel.requestTopup(inputBankId, binding.inputNominal.text.toString().replace(",", ""))
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
            val multipleCheck = nominal % 1000.0
            when {
                nominal < config.topupMinimum -> {
                    nominalValid = false
                    binding.inputNominalLayout.error = getString(
                        R.string.nominal_minimum_message,
                        "Rp.${ViewUtils.formatCurrency(config.topupMinimum)},-"
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
        validateNominal()
        return nominalValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.requestStatus.collect { state -> handleRequestState(state) } }
            }
        }
    }

    private fun handleConfigState(state: UiState<RequestTopupConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_topup))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
            }
            else -> Unit
        }
    }

    private fun handleRequestState(state: UiState<TopupRequestResult>) {
        binding.btnTopup.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_topup))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ ->
                        if (state.data.success) {
                            val intent = Intent(this, CustomerVerificationTopupActivity::class.java).apply {
                                putExtra("topup_id", state.data.topupId)
                                addFlags(Intent.FLAG_ACTIVITY_FORWARD_RESULT)
                            }
                            startActivity(intent)
                            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
                            finish()
                        }
                    }
                    .show()
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_topup))
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
