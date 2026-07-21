package com.rentone.user.presentation.feature.partner.rentvehicle.config
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
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityPartnerRentVehicleConfigBinding
import com.rentone.user.domain.model.RentVehicleConfig
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.Locale
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class PartnerRentVehicleConfigActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerRentVehicleConfigBinding
    private val viewModel: PartnerRentVehicleConfigViewModel by viewModels()

    private var deliveryFeeValid = false
    private var pickOffFeeValid = false
    private var overtimeFeeValid = false
    private var maxDayCodValid = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerRentVehicleConfigBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        observeState()
        viewModel.loadConfig()
    }

    private fun setupForm(config: RentVehicleConfig) {
        binding.swForceWithDriver.isChecked = config.forceWithDriver == 1
        binding.swForceDisableDelivry.isChecked = config.forceDisableDelivery == 1
        binding.swForceDisablePickOff.isChecked = config.forceDisablePickoff == 1

        binding.inputDeliveryFee.setText(formatFee(config.deliveryFee ?: 0.0))
        binding.inputDeliveryFee.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputDeliveryFee, s)
                validateDeliveryFee()
            }
        })

        binding.inputPickOffFee.setText(formatFee(config.pickoffFee ?: 0.0))
        binding.inputPickOffFee.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputPickOffFee, s)
                validatePickOffFee()
            }
        })

        binding.inputOvertimeFee.setText(formatFee(config.overtimeFee ?: 0.0))
        binding.inputOvertimeFee.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputOvertimeFee, s)
                validateOvertimeFee()
            }
        })

        binding.inputMaxDayCOD.setText(config.maxDayCod.toString())
        binding.inputMaxDayCOD.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateMaxDayCod()
        })

        binding.btnSave.setOnClickListener {
            if (validateForm()) postSaveConfig() else Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
        }
    }

    private fun formatFee(value: Double): String =
        NumberFormat.getNumberInstance(Locale.GERMANY).format(value).replace(".", ",")

    private fun validateDeliveryFee() {
        deliveryFeeValid = binding.inputDeliveryFee.text.toString().isNotEmpty()
        binding.inputDeliveryFeeLayout.error = if (deliveryFeeValid) null else getString(R.string.delivery_fee_cannot_empty)
    }

    private fun validatePickOffFee() {
        pickOffFeeValid = binding.inputPickOffFee.text.toString().isNotEmpty()
        binding.inputPickOffFeeLayout.error = if (pickOffFeeValid) null else getString(R.string.pickOff_fee_cannot_empty)
    }

    private fun validateOvertimeFee() {
        overtimeFeeValid = binding.inputOvertimeFee.text.toString().isNotEmpty()
        binding.inputOvertimeFeeLayout.error = if (overtimeFeeValid) null else getString(R.string.overtime_fee_cannot_empty)
    }

    private fun validateMaxDayCod() {
        maxDayCodValid = binding.inputMaxDayCOD.text.toString().isNotEmpty()
        binding.inputMaxDayCODLayout.error = if (maxDayCodValid) null else getString(R.string.max_day_cod_cannot_empty)
    }

    private fun validateForm(): Boolean {
        validateDeliveryFee()
        validatePickOffFee()
        validateOvertimeFee()
        validateMaxDayCod()
        return deliveryFeeValid && pickOffFeeValid && overtimeFeeValid && maxDayCodValid
    }

    private fun postSaveConfig() {
        val form = mapOf(
            "force_with_driver" to if (binding.swForceWithDriver.isChecked) "1" else "0",
            "force_disable_delivery" to if (binding.swForceDisableDelivry.isChecked) "1" else "0",
            "force_disable_pickoff" to if (binding.swForceDisablePickOff.isChecked) "1" else "0",
            "delivery_fee" to binding.inputDeliveryFee.text.toString().replace(",", ""),
            "pickoff_fee" to binding.inputPickOffFee.text.toString().replace(",", ""),
            "overtime_fee" to binding.inputOvertimeFee.text.toString().replace(",", ""),
            "max_day_cod" to binding.inputMaxDayCOD.text.toString()
        )
        viewModel.saveConfig(form)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.saveState.collect { state -> handleSaveState(state) } }
            }
        }
    }

    private fun handleConfigState(state: UiState<RentVehicleConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.rent_vehicle_config))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleSaveState(state: UiState<OperationResult>) {
        binding.btnSave.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.rent_vehicle_config))
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
                    .setTitle(getString(R.string.rent_vehicle_config))
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
