package com.rentone.user.presentation.feature.partner.rentvehicle.itemdetail
import android.app.Activity
import android.content.Intent
import android.graphics.BitmapFactory
import android.graphics.Color
import android.graphics.drawable.ColorDrawable
import android.os.Bundle
import android.util.Base64
import android.view.MenuItem
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.zoomimage.ZoomImageListActivity
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Config
import com.rentone.user.core.common.UiState
import com.rentone.user.core.ui.ImagePagerAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityPartnerRentVehicleItemDetailBinding
import com.rentone.user.domain.model.Vehicle
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.partner.rentvehicle.addvehicle.PartnerAddVehicleActivity

@AndroidEntryPoint
class PartnerRentVehicleItemDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerRentVehicleItemDetailBinding
    private val viewModel: PartnerRentVehicleItemDetailViewModel by viewModels()

    private var id = 0
    private var vehicle: Vehicle? = null

    private val editLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadDetail(id)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerRentVehicleItemDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        id = intent.getIntExtra("id", 0)

        observeState()
        viewModel.loadDetail(id)
    }

    private fun bindDetail(vehicle: Vehicle) {
        this.vehicle = vehicle
        title = vehicle.title

        binding.carouselView.isVisible = vehicle.photos.isNotEmpty()
        binding.carouselView.adapter = ImagePagerAdapter(
            urls = vehicle.photos.map { Config.BASE_VEHICLE_IMAGE + it.img }
        ) { position ->
            val intent = Intent(this, ZoomImageListActivity::class.java)
            intent.putExtra("photo", Config.BASE_VEHICLE_IMAGE + vehicle.photos[position].img)
            startActivity(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.txtTitle.text = vehicle.title

        bindIconField(binding.txtVehicleType, binding.iconVehicleType, vehicle.vehicleTypeName, vehicle.vehicleTypeIcon, R.string.vehicle_type)
        bindIconField(binding.txtBrand, binding.iconBrand, vehicle.brandName, vehicle.brandIcon, R.string.brand)
        bindIconField(binding.txtVehicleModel, binding.iconVehicleModel, vehicle.vehicleModelName, vehicle.vehicleModelIcon, R.string.model)
        bindIconField(binding.txtTransmition, binding.iconTransmition, vehicle.transmitionTypeName, vehicle.transmitionTypeIcon, R.string.transmition)
        bindIconField(binding.txtDrivenType, binding.iconDrivenType, vehicle.drivenTypeName, vehicle.drivenTypeIcon, R.string.driven)
        bindIconField(binding.txtFuel, binding.iconFuel, vehicle.fuelTypeName, vehicle.fuelTypeIcon, R.string.driven)

        binding.txtMaxPassenger.text = if (vehicle.maxPassenger > 0) vehicle.maxPassenger.toString() else "N/A"
        binding.txtMaxBaggage.text = if (vehicle.maxBaggage > 0) vehicle.maxBaggage.toString() else "N/A"
        binding.txtYear.text = vehicle.year.toString()

        if (vehicle.colorName != null) {
            binding.txtColor.text = vehicle.colorName
            binding.colorBox.isVisible = vehicle.colorValue != null
            vehicle.colorValue?.let { binding.colorBox.setImageDrawable(ColorDrawable(Color.parseColor(it))) }
        } else {
            binding.colorBox.isVisible = false
            binding.txtColor.text = "${getString(R.string.color)}\n\nN/A"
        }

        binding.priceContainer.isVisible = vehicle.price > 0.0
        if (vehicle.price > 0.0) binding.txtPrice.text = "Rp. ${ViewUtils.formatCurrency(vehicle.price)},-"

        binding.priceWithDriverBasicContainer.isVisible = vehicle.priceWithDriverBasic > 0.0
        if (vehicle.priceWithDriverBasic > 0.0) {
            binding.txtPriceWithDriverBasic.text = "Rp. ${ViewUtils.formatCurrency(vehicle.priceWithDriverBasic)},-"
        }

        binding.priceWithDriverFullContainer.isVisible = vehicle.priceWithDriverFull > 0.0
        if (vehicle.priceWithDriverFull > 0.0) {
            binding.txtPriceWithDriverFull.text = "Rp. ${ViewUtils.formatCurrency(vehicle.priceWithDriverFull)},-"
        }

        binding.txtWithDriver.text = getString(
            if (vehicle.withDriver == 0) R.string.optional_if_available else R.string.must_with_driver
        )
        binding.txtStatus.text = vehicle.statusName

        binding.btnEdit.setOnClickListener {
            val intent = Intent(this, PartnerAddVehicleActivity::class.java).apply {
                putExtra("functional_type", vehicle.functionalType)
                putExtra("id", vehicle.id)
                putExtra("edit", true)
            }
            editLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.btnDelete.setOnClickListener { confirmDelete() }
    }

    private fun bindIconField(text: TextView, icon: ImageView, name: String?, iconBase64: String?, fallbackLabelResId: Int) {
        if (name != null) {
            text.text = name
            icon.setImageBitmap(null)
            if (iconBase64 != null) {
                icon.isVisible = true
                val decodedString = Base64.decode(iconBase64, Base64.DEFAULT)
                val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
                icon.setImageBitmap(decodedByte)
                icon.setColorFilter(ContextCompat.getColor(this, R.color.colorAccent))
            } else {
                icon.isVisible = false
            }
        } else {
            icon.isVisible = false
            text.text = "${getString(fallbackLabelResId)}\n\nN/A"
        }
    }

    private fun confirmDelete() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.delete_vehicle))
            .setMessage(getString(R.string.confirm_delete_vehicle))
            .setNegativeButton(android.R.string.cancel, null)
            .setPositiveButton(R.string.yes) { _, _ -> viewModel.deleteVehicle(id) }
            .show()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.detail.collect { state -> handleDetailState(state) } }
                launch { viewModel.deleteState.collect { state -> handleDeleteState(state) } }
            }
        }
    }

    private fun handleDetailState(state: UiState<Vehicle>) {
        when (state) {
            is UiState.Success -> bindDetail(state.data)
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.menu_rent_vehicle))
                    .setMessage(getString(R.string.failed_check_to_server))
                    .setPositiveButton(R.string.yes) { _, _ -> finish() }
                    .show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleDeleteState(state: UiState<OperationResult>) {
        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.delete_vehicle))
                    .setMessage(getString(R.string.success_delete_vehicle))
                    .setPositiveButton(R.string.yes) { _, _ ->
                        setResult(Activity.RESULT_OK)
                        finish()
                    }
                    .show()
            }
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
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
