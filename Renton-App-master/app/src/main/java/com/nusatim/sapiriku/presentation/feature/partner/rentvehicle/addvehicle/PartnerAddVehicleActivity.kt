package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.addvehicle
import android.app.Activity
import android.graphics.Color
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.MenuItem
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.ImageView
import android.widget.ScrollView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.google.android.material.textfield.TextInputEditText
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.InputVehicleConfig
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.MenuUtils
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.custom.ArrayAdapterWithIcon
import com.nusatim.sapiriku.databinding.ActivityPartnerAddVehicleBinding
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.Vehicle
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.Locale
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.core.util.setBackPressedHandler
import com.nusatim.sapiriku.core.ui.CheckView

@AndroidEntryPoint
class PartnerAddVehicleActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerAddVehicleBinding
    private val viewModel: PartnerAddVehicleViewModel by viewModels()

    private var isEdit = false
    private var id = 0
    private var functionalType = 0
    private var config: InputVehicleConfig? = null

    private var inputVehicleTypeId = -1
    private var inputBrandId = -1
    private var inputVehicleModelId = -1
    private var inputColorId = -1
    private var inputTransmitionTypeId = -1
    private var inputDrivenTypeId = -1
    private var inputFuelId = -1

    private var titleValid = false
    private var maxPassengerValid = false
    private var maxBaggageValid = false
    private var yearValid = false
    private var priceValid = false
    private var priceWithDriverBasicValid = false
    private var priceWithDriverFullValid = false

    private lateinit var photoManager: VehiclePhotoManager

    private val pickImagesLauncher = registerForActivityResult(
        ActivityResultContracts.PickMultipleVisualMedia()
    ) { uris ->
        uris.forEach { uri -> photoManager.add(uri) }
        binding.imageContainerScroll.post { binding.imageContainerScroll.fullScroll(ScrollView.FOCUS_RIGHT) }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerAddVehicleBinding.inflate(layoutInflater)
        setContentView(binding.root)

        photoManager = VehiclePhotoManager(
            context = this,
            lifecycleScope = lifecycleScope,
            container = binding.imageContainer,
            preview = binding.photoPreview,
            uploadPhoto = { uri -> viewModel.uploadPhoto(uri) },
            deletePhoto = { id -> viewModel.deletePhoto(id) }
        )

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        isEdit = intent.getBooleanExtra("edit", false)
        id = intent.getIntExtra("id", 0)
        functionalType = intent.getIntExtra("functional_type", 0)
        title = getString(if (isEdit) R.string.edit_vehicle else R.string.add_vehicle)
        if (isEdit) binding.btnSaveVehicle.setText(R.string.edit_vehicle)

        binding.btnAddPhoto.setOnClickListener {
            pickImagesLauncher.launch(androidx.activity.result.PickVisualMediaRequest(ActivityResultContracts.PickVisualMedia.ImageOnly))
        }

        binding.btnSaveVehicle.setOnClickListener {
            if (validateForm()) postAddVehicle() else Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
        }

        observeState()
        viewModel.loadConfig(functionalType)
        setBackPressedHandler { handleBackPress() }
    }

    private fun setupForm(config: InputVehicleConfig) {
        this.config = config

        binding.inputTitle.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateTitle()
        })

        binding.inputVehicleType.setOnClickListener {
            bindBasicDataPicker(binding.inputVehicleType, binding.checkVehicleType, config.vehicleType,
                onSelect = { inputVehicleTypeId = it.id },
                onClear = { inputVehicleTypeId = -1 })
        }

        binding.inputBrand.setOnClickListener {
            bindBasicDataPicker(binding.inputBrand, binding.checkBrand, config.brand,
                onSelect = { data ->
                    if (inputBrandId != data.id) {
                        inputVehicleModelId = -1
                        binding.inputModel.setText(null)
                        binding.checkModel.uncheck()
                    }
                    inputBrandId = data.id
                },
                onClear = { inputBrandId = -1 })
        }

        binding.inputModel.setOnClickListener { viewModel.loadModels(inputBrandId) }

        binding.inputMaxPassenger.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateMaxPassenger()
        })

        binding.inputMaxBaggage.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateMaxBaggage()
        })

        binding.inputYear.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) = validateYear()
        })

        binding.inputColor.setOnClickListener {
            val adapter = object : ArrayAdapter<BasicData>(this, R.layout.simple_list_color_item_1, android.R.id.text1, config.color) {
                override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
                    val view = super.getView(position, convertView, parent)
                    view.findViewById<ImageView>(R.id.colorBox).setBackgroundColor(Color.parseColor(config.color[position].value))
                    return view
                }
            }
            MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
                val data = adapter.getItem(which)!!
                inputColorId = data.id
                binding.inputColor.setText(data.name)
                binding.colorBox.setBackgroundColor(Color.parseColor(data.value))
                binding.checkColor.check()
            }, { _, _ ->
                inputColorId = -1
                binding.inputColor.setText(null)
                binding.colorBox.setBackgroundColor(0)
                binding.checkColor.uncheck()
            })
        }

        binding.inputTransmitionType.setOnClickListener {
            bindBasicDataPicker(binding.inputTransmitionType, binding.checkTransmitionType, config.transmitionType,
                onSelect = { inputTransmitionTypeId = it.id },
                onClear = { inputTransmitionTypeId = -1 })
        }

        binding.inputDrivenType.setOnClickListener {
            bindBasicDataPicker(binding.inputDrivenType, binding.checkDriven, config.drivenType,
                onSelect = { inputDrivenTypeId = it.id },
                onClear = { inputDrivenTypeId = -1 })
        }

        binding.inputFuel.setOnClickListener {
            bindBasicDataPicker(binding.inputFuel, binding.checkFuel, config.fuel,
                onSelect = { inputFuelId = it.id },
                onClear = { inputFuelId = -1 })
        }

        binding.inputPrice.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputPrice, s)
                validatePrice()
            }
        })

        binding.inputPriceWithDriverBasic.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputPriceWithDriverBasic, s)
                validatePriceWithDriverBasic()
            }
        })

        binding.inputPriceWithDriverFull.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) = Unit
            override fun afterTextChanged(s: Editable) {
                ViewUtils.numberToDecimalText(this, binding.inputPriceWithDriverFull, s)
                validatePriceWithDriverFull()
            }
        })

        if (isEdit) {
            viewModel.loadVehicleDetail(id)
        }
    }

    private fun bindVehicleDetail(vehicle: Vehicle) {
        binding.inputTitle.setText(vehicle.title)

        binding.inputVehicleType.setText(vehicle.vehicleTypeName)
        inputVehicleTypeId = vehicle.vehicleType
        if (vehicle.vehicleTypeName != null) binding.checkVehicleType.check() else binding.checkVehicleType.uncheck()

        binding.inputBrand.setText(vehicle.brandName)
        inputBrandId = vehicle.brandId
        if (vehicle.brandName != null) binding.checkBrand.check() else binding.checkBrand.uncheck()

        binding.inputModel.setText(vehicle.vehicleModelName)
        inputVehicleModelId = vehicle.vehicleModel
        if (vehicle.vehicleModelName != null) binding.checkModel.check() else binding.checkModel.uncheck()

        binding.inputMaxPassenger.setText(vehicle.maxPassenger.toString())
        binding.inputMaxBaggage.setText(vehicle.maxBaggage.toString())
        binding.inputYear.setText(vehicle.year.toString())

        binding.inputColor.setText(vehicle.colorName)
        inputColorId = vehicle.colorId
        if (vehicle.colorValue != null) {
            binding.colorBox.setBackgroundColor(Color.parseColor(vehicle.colorValue))
        } else {
            binding.colorBox.setBackgroundColor(0)
        }
        if (vehicle.colorName != null) binding.checkColor.check() else binding.checkColor.uncheck()

        binding.inputTransmitionType.setText(vehicle.transmitionTypeName)
        inputTransmitionTypeId = vehicle.transmitionType
        if (vehicle.transmitionTypeName != null) binding.checkTransmitionType.check() else binding.checkTransmitionType.uncheck()

        binding.inputDrivenType.setText(vehicle.drivenTypeName)
        inputDrivenTypeId = vehicle.drivenType
        if (vehicle.drivenTypeName != null) binding.checkDriven.check() else binding.checkDriven.uncheck()

        binding.inputPrice.setText(formatFee(vehicle.price))
        binding.inputPriceWithDriverBasic.setText(formatFee(vehicle.priceWithDriverBasic))
        binding.inputPriceWithDriverFull.setText(formatFee(vehicle.priceWithDriverFull))

        binding.inputFuel.setText(vehicle.fuelTypeName)
        inputFuelId = vehicle.fuelType
        if (vehicle.fuelTypeName != null) binding.checkFuel.check() else binding.checkFuel.uncheck()

        binding.swWithDriver.isChecked = vehicle.withDriver == 1
        binding.swDelivered.isChecked = vehicle.delivered == 1
        binding.swPickOff.isChecked = vehicle.pickoff == 1
        binding.swStatus.isChecked = vehicle.status == 1

        photoManager.bindExisting(vehicle.photos)
    }

    private fun formatFee(value: Double): String =
        NumberFormat.getNumberInstance(Locale.GERMANY).format(value).replace(".", ",")

    /** Shared popup-list binding for the (name, id) dropdown fields that all follow the same select/clear pattern. */
    private fun bindBasicDataPicker(
        input: TextInputEditText,
        check: CheckView,
        items: List<BasicData>,
        onSelect: (BasicData) -> Unit,
        onClear: () -> Unit
    ) {
        val adapter = ArrayAdapterWithIcon(this, ArrayList(items))
        MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
            val data: BasicData = adapter.getItem(which)!!
            input.setText(data.name)
            check.check()
            onSelect(data)
        }, { _, _ ->
            input.setText(null)
            check.uncheck()
            onClear()
        })
    }

    private fun validateTitle() {
        titleValid = binding.inputTitle.text.toString().isNotEmpty()
        binding.inputTitleLayout.error = if (titleValid) null else getString(R.string.title_cannot_empty)
    }

    private fun validateMaxPassenger() {
        maxPassengerValid = binding.inputMaxPassenger.text.toString().isNotEmpty()
        binding.inputMaxPassengerLayout.error = if (maxPassengerValid) null else getString(R.string.max_passenger_cannot_empty)
    }

    private fun validateMaxBaggage() {
        maxBaggageValid = binding.inputMaxBaggage.text.toString().isNotEmpty()
        binding.inputMaxBaggageLayout.error = if (maxBaggageValid) null else getString(R.string.max_baggage_cannot_empty)
    }

    private fun validateYear() {
        yearValid = binding.inputYear.text.toString().isNotEmpty()
        binding.inputYearLayout.error = if (yearValid) null else getString(R.string.year_cannot_empty)
    }

    private fun validatePrice() {
        priceValid = binding.inputPrice.text.toString().isNotEmpty()
        binding.inputPriceLayout.error = if (priceValid) null else getString(R.string.price_cannot_empty)
    }

    private fun validatePriceWithDriverBasic() {
        priceWithDriverBasicValid = binding.inputPriceWithDriverBasic.text.toString().isNotEmpty()
        binding.inputPriceWithDriverBasicLayout.error = if (priceWithDriverBasicValid) null else getString(R.string.price_cannot_empty)
    }

    private fun validatePriceWithDriverFull() {
        priceWithDriverFullValid = binding.inputPriceWithDriverFull.text.toString().isNotEmpty()
        binding.inputPriceWithDriverFullLayout.error = if (priceWithDriverFullValid) null else getString(R.string.price_cannot_empty)
    }

    private fun validateForm(): Boolean {
        validateTitle()
        validateYear()
        validateMaxPassenger()
        validatePrice()
        validatePriceWithDriverBasic()
        validatePriceWithDriverFull()
        return titleValid && yearValid && maxPassengerValid && priceValid && priceWithDriverBasicValid && priceWithDriverFullValid
    }

    private fun postAddVehicle() {
        val form = buildMap {
            if (isEdit) put("id", id.toString())
            put("title", binding.inputTitle.text.toString())
            if (inputVehicleTypeId != -1) put("vehicle_type", inputVehicleTypeId.toString())
            if (inputBrandId != -1) put("brand_id", inputBrandId.toString())
            if (inputVehicleModelId != -1) put("vehicle_model", inputVehicleModelId.toString())
            put("max_passenger", binding.inputMaxPassenger.text.toString())
            put("max_baggage", binding.inputMaxBaggage.text.toString())
            put("year", binding.inputYear.text.toString())
            if (inputColorId != -1) put("color_id", inputColorId.toString())
            if (inputTransmitionTypeId != -1) put("transmition_type", inputTransmitionTypeId.toString())
            if (inputDrivenTypeId != -1) put("driven_type", inputDrivenTypeId.toString())
            if (inputFuelId != -1) put("fuel_type", inputFuelId.toString())
            put("price", binding.inputPrice.text.toString().replace(",", ""))
            put("price_with_driver_basic", binding.inputPriceWithDriverBasic.text.toString().replace(",", ""))
            put("price_with_driver_full", binding.inputPriceWithDriverFull.text.toString().replace(",", ""))
            put("with_driver", if (binding.swWithDriver.isChecked) "1" else "0")
            put("delivered", if (binding.swDelivered.isChecked) "1" else "0")
            put("pickoff", if (binding.swPickOff.isChecked) "1" else "0")
            put("status", if (binding.swStatus.isChecked) "1" else "0")
            put("functional_type", functionalType.toString())
        }

        val files = photoManager.newPhotoPaths()

        viewModel.saveVehicle(form, files)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.config.collect { state -> handleConfigState(state) } }
                launch { viewModel.vehicleDetail.collect { state -> handleVehicleDetailState(state) } }
                launch {
                    viewModel.models.collect { state ->
                        if (state is UiState.Success) openSelectorVehicleModel(state.data)
                    }
                }
                launch { viewModel.saveState.collect { state -> handleSaveState(state) } }
            }
        }
    }

    private fun openSelectorVehicleModel(models: List<BasicData>) {
        val adapter = ArrayAdapter(this, android.R.layout.simple_list_item_1, models)
        MenuUtils.buildPopupList(this, "", R.drawable.ic_car, adapter, { _, which ->
            val data = adapter.getItem(which)!!
            inputVehicleModelId = data.id
            binding.inputModel.setText(data.name)
            binding.checkModel.check()
        })
    }

    private fun handleConfigState(state: UiState<InputVehicleConfig>) {
        when (state) {
            is UiState.Success -> setupForm(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_vehicle))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes, null)
                    .show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleVehicleDetailState(state: UiState<Vehicle>) {
        when (state) {
            is UiState.Success -> bindVehicleDetail(state.data)
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleSaveState(state: UiState<OperationResult>) {
        binding.btnSaveVehicle.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.post_vehicle))
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
                    .setTitle(getString(R.string.post_vehicle))
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
