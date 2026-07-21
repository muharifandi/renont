package com.rentone.user.presentation.feature.rentvehicle.checkout
import android.app.Activity
import android.app.TimePickerDialog
import android.content.Context
import android.content.Intent
import android.view.View
import android.widget.Button
import android.widget.Switch
import androidx.activity.result.ActivityResultCaller
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.view.isVisible
import androidx.core.widget.doAfterTextChanged
import com.google.android.gms.maps.model.LatLng
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.locationpick.LocationPickActivity
import com.rentone.user.core.util.ViewUtils
import java.util.Calendar

/**
 * Owns one delivery/pick-off option on the checkout screen: the enable switch, address + time
 * inputs and their validation, and the map-based location picker. Used twice by
 * [RentVehicleOrderCheckoutActivity] (once for delivery, once for pick-off) since both follow
 * an identical pattern.
 */
class CheckoutDeliveryOptionController(
    private val context: Context,
    caller: ActivityResultCaller,
    private val switchView: Switch,
    private val inputContainer: View,
    private val feeContainer: View,
    private val addressInput: TextInputEditText,
    private val addressLayout: TextInputLayout,
    private val timeInput: TextInputEditText,
    private val timeLayout: TextInputLayout,
    private val pickLocationButton: Button,
    initialTime: String?,
    private val onChanged: () -> Unit
) {
    var location: LatLng? = null
        private set

    private var addressValid = false
    private var timeValid = false

    private val pickLocationLauncher = caller.registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            location = LatLng(
                result.data?.getDoubleExtra("latitude", 0.0) ?: 0.0,
                result.data?.getDoubleExtra("longitude", 0.0) ?: 0.0
            )
        }
    }

    val isEnabled: Boolean get() = switchView.isChecked
    val addressText: String get() = addressInput.text.toString()
    val timeText: String get() = timeInput.text.toString()

    init {
        switchView.setOnCheckedChangeListener { _, isChecked ->
            inputContainer.isVisible = isChecked
            feeContainer.isVisible = isChecked
            onChanged()
        }

        addressInput.doAfterTextChanged { validateAddress() }
        pickLocationButton.setOnClickListener {
            val intent = Intent(context, LocationPickActivity::class.java)
            location?.let {
                intent.putExtra("latitude", it.latitude)
                intent.putExtra("longitude", it.longitude)
            }
            pickLocationLauncher.launch(intent)
        }

        timeInput.setText(initialTime)
        timeInput.setOnClickListener {
            showTimePicker(timeInput.text.toString()) { time ->
                timeInput.setText(time)
                validateTime()
            }
        }
    }

    fun validate(): Boolean {
        validateAddress()
        validateTime()
        return !isEnabled || (addressValid && timeValid)
    }

    private fun validateAddress() {
        addressValid = addressInput.text.toString().isNotEmpty()
        addressLayout.error = if (addressValid) null else context.getString(R.string.address_cannot_empty)
    }

    private fun validateTime() {
        timeValid = timeInput.text.toString() != "- : -"
        timeLayout.error = if (timeValid) null else context.getString(R.string.time_cannot_empty)
    }

    private fun showTimePicker(current: String, onSet: (String) -> Unit) {
        val now = Calendar.getInstance()
        val dialog = TimePickerDialog(
            context,
            { _, hourOfDay, minute -> onSet("${ViewUtils.convertDate(hourOfDay)}:${ViewUtils.convertDate(minute)}") },
            now.get(Calendar.HOUR_OF_DAY),
            now.get(Calendar.MINUTE),
            true
        )
        val parts = current.split(":")
        if (parts.size == 2) {
            val hour = parts[0].toIntOrNull()
            val minute = parts[1].toIntOrNull()
            if (hour != null && minute != null) dialog.updateTime(hour, minute)
        }
        dialog.show()
    }
}
