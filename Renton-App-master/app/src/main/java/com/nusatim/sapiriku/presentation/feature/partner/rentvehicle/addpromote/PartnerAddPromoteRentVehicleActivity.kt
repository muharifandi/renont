package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.addpromote
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.MenuItem
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.InputPromoteRentVehicleConfig
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.MenuUtils
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.core.util.getSerializableExtraCompat
import com.nusatim.sapiriku.databinding.ActivityPartnerAddPromoteRentVehicleBinding
import com.nusatim.sapiriku.databinding.ItemPartnerListVehicleBinding
import com.nusatim.sapiriku.domain.model.Vehicle
import com.nusatim.sapiriku.presentation.feature.rentvehicle.datepicker.RentVehicleDatePickerActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.Locale
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.core.util.setBackPressedHandler

@AndroidEntryPoint
class PartnerAddPromoteRentVehicleActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerAddPromoteRentVehicleBinding
    private val viewModel: PartnerAddPromoteRentVehicleViewModel by viewModels()

    private var config: InputPromoteRentVehicleConfig? = null
    private var inputVehicleId = -1
    private var startDate: String? = null
    private var endDate: String? = null
    private var days = 0

    private var vehicleValid = false
    private var dateValid = false

    private val datePickerLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val param = result.data?.getSerializableExtraCompat<HashMap<String, String>>("param") ?: return@registerForActivityResult
            onDateRangeSelected(param)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerAddPromoteRentVehicleBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.btnSavePromote.setOnClickListener {
            if (validateForm()) postAddPromote() else Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
        }

        observeState()
        viewModel.loadConfig()
        setBackPressedHandler { handleBackPress() }
    }

    private fun setupForm(config: InputPromoteRentVehicleConfig) {
        this.config = config

        binding.cvInfo.isVisible = !config.info.isNullOrEmpty()
        binding.txtInfo.text = config.info

        binding.inputVehicle.setOnClickListener {
            val adapter = object : ArrayAdapter<Vehicle>(this, 0, config.vehicles) {
                override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
                    val itemBinding = if (convertView == null) {
                        ItemPartnerListVehicleBinding.inflate(LayoutInflater.from(context), parent, false)
                    } else {
                        ItemPartnerListVehicleBinding.bind(convertView)
                    }
                    val vehicle = config.vehicles[position]

                    if (vehicle.img != null) {
                        itemBinding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + vehicle.img) {
                            error(R.drawable.ic_broken_image)
                        }
                    } else {
                        itemBinding.previewVehicle.setImageResource(R.drawable.no_image)
                    }

                    itemBinding.txtTitle.text = vehicle.title
                    itemBinding.txtVehicleType.text = vehicle.vehicleTypeName
                    itemBinding.containerWIthDriver.isVisible = vehicle.withDriver == 1
                    itemBinding.txtMaxPassenger.text = vehicle.maxPassenger.toString()

                    itemBinding.containerColor.isVisible = vehicle.colorName != null
                    if (vehicle.colorName != null) {
                        itemBinding.txtColor.text = vehicle.colorName
                        itemBinding.colorBox.setCardBackgroundColor(android.graphics.Color.parseColor(vehicle.colorValue))
                    }

                    itemBinding.txtPrice.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(vehicle.price)},-"

                    return itemBinding.root
                }
            }

            MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
                val data = adapter.getItem(which)!!
                inputVehicleId = data.id
                binding.inputVehicle.setText(data.title)
                binding.checkVehicle.check()
                vehicleValid = true

                binding.txtTitle.text = data.title
                if (data.img != null) {
                    binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + data.img) { error(R.drawable.ic_broken_image) }
                } else {
                    binding.previewVehicle.setImageResource(R.drawable.no_image)
                }
            })
        }

        binding.inputDate.setOnClickListener {
            val intent = Intent(this, RentVehicleDatePickerActivity::class.java).apply {
                putExtra("disableTime", true)
                putExtra("dayAdd", 1)
            }
            datePickerLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    private fun onDateRangeSelected(param: HashMap<String, String>) {
        startDate = param["start_date"]
        endDate = param["end_date"]
        days = (ViewUtils.getCountOfDays(startDate.orEmpty(), endDate.orEmpty()).toIntOrNull() ?: 0) + 1

        val rangeText = "${ViewUtils.mysqlDateToNormalDate(startDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
            ViewUtils.mysqlDateToNormalDate(endDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")
        binding.inputDate.setText(rangeText)
        binding.txtDate.text = rangeText
        binding.txtDays.text = "$days ${getString(R.string.days)}"

        val pricePerDay = config?.pricePerDay ?: 0.0
        binding.txtPricePerDay.text = "Rp. ${ViewUtils.formatCurrency(pricePerDay)},- ${getString(R.string.per_day)}"

        binding.checkDate.check()
        dateValid = true
        calculatePayment()
    }

    private fun calculatePayment() {
        val pricePerDay = config?.pricePerDay ?: 0.0
        val totalPayment = pricePerDay * days
        binding.txtTotalPayment.text = "Rp. ${ViewUtils.formatCurrency(totalPayment)},- "
    }

    private fun validateVehicle() {
        vehicleValid = inputVehicleId != -1
        binding.inputVehicleLayout.error = if (vehicleValid) null else getString(R.string.vehicle_cannot_empty)
    }

    private fun validateDate() {
        dateValid = binding.inputDate.text.toString().isNotEmpty()
        binding.inputDateLayout.error = if (dateValid) null else getString(R.string.date_cannot_empty)
    }

    private fun validateForm(): Boolean {
        validateVehicle()
        validateDate()
        return vehicleValid && dateValid
    }

    private fun postAddPromote() {
        val start = startDate ?: return
        val end = endDate ?: return
        viewModel.postPromote(inputVehicleId, start, end)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.saveState.collect { state -> handleSaveState(state) } }
            }
        }
    }

    private fun handleConfigState(state: UiState<InputPromoteRentVehicleConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.menu_add_promote_rent_vehicle))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleSaveState(state: UiState<OperationResult>) {
        binding.btnSavePromote.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_promote))
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
                    .setTitle(getString(R.string.post_promote))
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
