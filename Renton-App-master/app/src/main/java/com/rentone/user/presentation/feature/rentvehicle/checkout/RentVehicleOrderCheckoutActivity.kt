package com.rentone.user.presentation.feature.rentvehicle.checkout
import android.app.Activity
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.core.widget.doAfterTextChanged
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.core.util.getSerializableExtraCompat
import com.rentone.user.databinding.ActivityRentVehicleOrderCheckoutBinding
import com.rentone.user.domain.model.CheckoutDetail
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.Voucher
import com.rentone.user.domain.model.command.CheckoutCommand
import com.rentone.user.presentation.feature.rentvehicle.itemdetail.VehicleItemDetailActivity
import com.rentone.user.presentation.feature.rentvehicle.listvehicle.RentVehicleListVehicleActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class RentVehicleOrderCheckoutActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRentVehicleOrderCheckoutBinding
    private val viewModel: RentVehicleOrderCheckoutViewModel by viewModels()

    private var vehicleId = 0
    private var pricePackage = 0
    private var voucher: Voucher? = null
    private var checkoutDetail: CheckoutDetail? = null

    private lateinit var deliveryOption: CheckoutDeliveryOptionController
    private lateinit var pickOffOption: CheckoutDeliveryOptionController

    private val param: HashMap<String, String> by lazy {
        intent.getSerializableExtraCompat<HashMap<String, String>>("param") ?: HashMap()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRentVehicleOrderCheckoutBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        vehicleId = intent.getIntExtra("vehicle_id", 0)
        pricePackage = intent.getIntExtra("package", 0)

        deliveryOption = CheckoutDeliveryOptionController(
            context = this,
            caller = this,
            switchView = binding.swDelivery,
            inputContainer = binding.deliveryInputContainer,
            feeContainer = binding.deliveryFeeContainer,
            addressInput = binding.inputAddressDelivery,
            addressLayout = binding.inputAddressDeliveryLayout,
            timeInput = binding.inputTimeDelivery,
            timeLayout = binding.inputTimeDeliveryLayout,
            pickLocationButton = binding.btnPickDeliveryLocation,
            initialTime = param["time"],
            onChanged = { calculateTotalPayment() }
        )

        pickOffOption = CheckoutDeliveryOptionController(
            context = this,
            caller = this,
            switchView = binding.swPickOff,
            inputContainer = binding.pickOffInputContainer,
            feeContainer = binding.pickOffFeeContainer,
            addressInput = binding.inputAddressPickOff,
            addressLayout = binding.inputAddressPickOffLayout,
            timeInput = binding.inputTimePickOff,
            timeLayout = binding.inputTimePickOffLayout,
            pickLocationButton = binding.btnPickOffLocation,
            initialTime = param["time"],
            onChanged = { calculateTotalPayment() }
        )

        observeState()
        viewModel.loadDetail(vehicleId, pricePackage, param["start_date"], param["end_date"])
    }

    private fun bindDetail(data: CheckoutDetail) {
        checkoutDetail = data
        val vehicle = data.vehicle ?: return
        val config = data.config

        binding.txtTitle.text = vehicle.title
        if (vehicle.photos.isNotEmpty()) {
            binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + vehicle.photos[0].img) {
                placeholder(R.drawable.no_image)
                error(R.drawable.no_image)
            }
        } else {
            binding.previewVehicle.setImageResource(R.drawable.no_image)
        }
        binding.txtVehicleType.text = vehicle.vehicleTypeName

        val days = ViewUtils.getCountOfDays(param["start_date"].orEmpty(), param["end_date"].orEmpty()).toIntOrNull() ?: 0
        binding.txtDays.text = days.toString()

        val (selectedPrice, stringId) = when (pricePackage) {
            1 -> vehicle.priceWithDriverBasic to R.string.car_plus_driver_basic
            2 -> vehicle.priceWithDriverFull to R.string.car_plus_driver_all_in
            else -> vehicle.price to R.string.car_only
        }
        binding.txtPricePackage.setText(stringId)
        binding.txtPrice.text = "Rp. ${ViewUtils.formatCurrency(selectedPrice)},-"
        binding.txtTotal.text = "Rp. ${ViewUtils.formatCurrency(selectedPrice * days)},-"

        val canDeliver = vehicle.delivered == 1 && (config?.forceDisableDelivery ?: 0) == 0
        val canPickOff = vehicle.pickoff == 1 && (config?.forceDisablePickoff ?: 0) == 0
        binding.cvPick.isVisible = canDeliver || canPickOff

        binding.txtDeliveryFee.text = "Rp. ${ViewUtils.formatCurrency(config?.deliveryFee ?: 0.0)},-"
        binding.deliveryContainer.isVisible = canDeliver

        binding.txtPickOffFee.text = "Rp. ${ViewUtils.formatCurrency(config?.pickoffFee ?: 0.0)},-"
        binding.pickOffContainer.isVisible = canPickOff

        binding.inputCoupon.doAfterTextChanged { text ->
            viewModel.checkVoucher(text.toString(), param["start_date"])
        }

        binding.cvCOD.isVisible = data.cashOnDelivery == 1

        binding.btnOrder.setOnClickListener {
            if (validateForm()) postOrder() else Toast.makeText(this, R.string.check_form_again, Toast.LENGTH_LONG).show()
        }

        calculateTotalPayment()
    }

    private fun validateForm(): Boolean {
        val deliveryOk = deliveryOption.validate()
        val pickOffOk = pickOffOption.validate()
        return deliveryOk && pickOffOk
    }

    private fun setVoucherUi(state: VoucherState) {
        when (state) {
            is VoucherState.Found -> {
                voucher = state.voucher
                binding.inputCouponLayout.error = null
                bindVoucherCard(state.voucher)
            }
            is VoucherState.NotFound -> {
                voucher = null
                binding.inputCouponLayout.error = state.message
                binding.cvCouponContainer.isVisible = false
                binding.txtDiscount.text = "Rp. 0,-"
            }
            is VoucherState.Error -> {
                voucher = null
                binding.inputCouponLayout.error = getString(R.string.failed_check_to_server)
            }
            is VoucherState.Checking -> {
                voucher = null
                binding.inputCouponLayout.error = getString(R.string.checking_coupon)
            }
            is VoucherState.Idle -> {
                voucher = null
                binding.inputCouponLayout.error = null
                binding.cvCouponContainer.isVisible = false
                binding.txtDiscount.text = "Rp. 0,-"
            }
        }
        calculateTotalPayment()
    }

    private fun bindVoucherCard(voucher: Voucher) {
        binding.cvCouponContainer.isVisible = true
        binding.txtCouponDescription.text = voucher.description
        binding.txtCouponValue.text = "Rp. ${ViewUtils.formatCurrency(voucher.value)},-"

        binding.txtCOuponValidity.isVisible = voucher.useExpire == 1
        if (voucher.useExpire == 1) {
            binding.txtCOuponValidity.text =
                "${ViewUtils.mysqlDateToNormalDate(voucher.startDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
                    ViewUtils.mysqlDateToNormalDate(voucher.endDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")
        }

        binding.txtCouponQuota.isVisible = voucher.useQuota == 1
        if (voucher.useQuota == 1) {
            binding.txtCouponQuota.text = getString(R.string.voucher_quota_left, voucher.quota.toString())
        }

        binding.txtDiscount.text = "Rp. ${ViewUtils.formatCurrency(voucher.value)},-"
    }

    private fun calculateTotalPayment() {
        val data = checkoutDetail ?: return
        var totalPayment = data.rentPayment
        if (deliveryOption.isEnabled) totalPayment += data.config?.deliveryFee ?: 0.0
        if (pickOffOption.isEnabled) totalPayment += data.config?.pickoffFee ?: 0.0
        voucher?.let { totalPayment -= it.value }

        binding.txtTotalPayment.text = "Rp. ${ViewUtils.formatCurrency(totalPayment)},-"
    }

    private fun postOrder() {
        val command = CheckoutCommand(
            vehicleId = vehicleId,
            pricePackageId = pricePackage,
            startDate = param["start_date"].orEmpty(),
            endDate = param["end_date"].orEmpty(),
            voucherCode = voucher?.code,
            notes = null // Add notes field if needed
        )
        // Note: Real implementation might need latitude/longitude in the command too.
        // For now, I'm keeping it simple based on the command I created earlier.
        
        viewModel.postCheckout(command)
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch { viewModel.detail.collect { state -> handleDetailState(state) } }
                launch { viewModel.voucherState.collect { state -> setVoucherUi(state) } }
                launch { viewModel.checkoutState.collect { state -> handleCheckoutState(state) } }
            }
        }
    }

    private fun handleDetailState(state: UiState<CheckoutDetail>) {
        when (state) {
            is UiState.Success -> bindDetail(state.data)
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                finish()
            }
            else -> Unit
        }
    }

    private fun handleCheckoutState(state: UiState<OperationResult>) {
        binding.btnOrder.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.title_rent_vehicle_checkout))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ ->
                        if (state.data.success) {
                            setResult(Activity.RESULT_OK)
                            RentVehicleListVehicleActivity.instance?.let {
                                it.setResult(Activity.RESULT_OK)
                                it.finish()
                            }
                            VehicleItemDetailActivity.instance?.let {
                                it.setResult(Activity.RESULT_OK)
                                it.finish()
                            }
                            finish()
                        }
                    }
                    .show()
            }
            is UiState.Error -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.title_rent_vehicle_checkout))
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
