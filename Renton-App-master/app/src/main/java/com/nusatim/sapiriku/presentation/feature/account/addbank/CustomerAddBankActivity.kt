package com.nusatim.sapiriku.presentation.feature.account.addbank
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
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.MenuUtils
import com.nusatim.sapiriku.custom.ArrayAdapterBankWithIcon
import com.nusatim.sapiriku.databinding.ActivityCustomerAddBankBinding
import com.nusatim.sapiriku.domain.model.Bank
import com.nusatim.sapiriku.domain.model.CustomerBank
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.core.util.setBackPressedHandler

@AndroidEntryPoint
class CustomerAddBankActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerAddBankBinding
    private val viewModel: CustomerAddBankViewModel by viewModels()

    private var isEdit = false
    private var id = 0
    private var inputBankId = -1
    private var nameValid = false
    private var bankNumberValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerAddBankBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        isEdit = intent.getBooleanExtra("edit", false)
        id = intent.getIntExtra("id", 0)
        title = getString(if (isEdit) R.string.edit_bank else R.string.add_bank)
        if (isEdit) {
            binding.btnSaveBank.setText(R.string.edit_bank)
        }

        binding.btnSaveBank.setOnClickListener {
            if (validateForm()) {
                viewModel.saveBank(
                    id = if (isEdit) id else null,
                    bankId = inputBankId,
                    name = binding.inputName.text.toString(),
                    bankNumber = binding.inputBankNumber.text.toString()
                )
            } else {
                Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
            }
        }

        observeState()
        viewModel.loadConfig()
        setBackPressedHandler { handleBackPress() }
    }

    private fun setupForm(banks: List<Bank>) {
        binding.checkBank.check()
        if (banks.isNotEmpty()) {
            inputBankId = banks[0].id
            binding.inputBank.setText(banks[0].name)
        }
        binding.inputBank.setOnClickListener {
            val adapter = ArrayAdapterBankWithIcon(this, ArrayList(banks))
            MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
                val data: Bank = adapter.getItem(which)!!
                inputBankId = data.id
                binding.inputBank.setText(data.name)
                binding.checkBank.check()
            })
        }

        binding.inputName.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateName()
        })

        binding.inputBankNumber.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateBankNumber()
        })

        if (isEdit) {
            viewModel.loadBankDetail(id)
        }
    }

    private fun setupBankDetail(detail: CustomerBank?) {
        val bank = detail ?: return
        binding.inputName.setText(bank.name)
        binding.inputBankNumber.setText(bank.bankNumber)
        binding.inputBank.setText(bank.bankName)
        inputBankId = bank.bankId
    }

    private fun validateName() {
        if (binding.inputName.text.toString().isNotEmpty()) {
            nameValid = true
            binding.inputNameLayout.error = null
            binding.checkName.check()
        } else {
            nameValid = false
            binding.inputNameLayout.error = getString(R.string.name_cannot_empty)
            binding.checkName.uncheck()
        }
    }

    private fun validateBankNumber() {
        if (binding.inputBankNumber.text.toString().isNotEmpty()) {
            bankNumberValid = true
            binding.inputBankNumberLayout.error = null
            binding.checkBankNumber.check()
        } else {
            bankNumberValid = false
            binding.inputBankNumberLayout.error = getString(R.string.bank_number_cannot_empty)
            binding.checkBankNumber.uncheck()
        }
    }

    private fun validateForm(): Boolean {
        validateName()
        validateBankNumber()
        return nameValid && bankNumberValid
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.bankDetail.collect { state -> handleBankDetailState(state) } }
                launch { viewModel.saveState.collect { state -> handleSaveState(state) } }
            }
        }
    }

    private fun handleConfigState(state: UiState<List<Bank>>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_bank))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleBankDetailState(state: UiState<CustomerBank?>) {
        when (state) {
            is UiState.Success -> setupBankDetail(state.data)
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleSaveState(state: UiState<OperationResult>) {
        binding.btnSaveBank.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_bank))
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
                    .setTitle(getString(R.string.post_bank))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
            }
            else -> Unit
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            handleBackPress()
        }
        return true
    }

    private fun handleBackPress() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.exit))
            .setMessage(getString(R.string.exit_confirm))
            .setNegativeButton(R.string.no, null)
            .setPositiveButton(R.string.yes) { _, _ -> finish() }
            .show()
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
