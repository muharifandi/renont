package com.rentone.user.presentation.feature.rentvehicle.listvehicle.adapter
import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.databinding.ItemListVehicleBinding
import com.rentone.user.domain.model.Vehicle
import java.text.DecimalFormat
import java.text.NumberFormat
import java.util.Locale

class ListVehicleAdapter(
    private val onItemClick: (Vehicle) -> Unit
) : LoadingFooterListAdapter<Vehicle>(DIFF_CALLBACK) {

    /** True when the active sort mode is "nearest distance" (legacy sortIndex == 5). */
    var showDistance: Boolean = false
        set(value) {
            if (field == value) return
            field = value
            notifyItemRangeChanged(0, itemCount)
        }

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        VehicleViewHolder(ItemListVehicleBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Vehicle, position: Int) {
        (holder as VehicleViewHolder).bind(item)
    }

    inner class VehicleViewHolder(private val binding: ItemListVehicleBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Vehicle) {
            val ctx = binding.root.context

            if (item.img != null) {
                binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + item.img) {
                    error(R.drawable.no_image)
                }
            } else {
                binding.previewVehicle.setImageResource(R.drawable.no_image)
            }

            binding.containerDistance.isVisible = showDistance
            if (showDistance) {
                val lessThan: String
                val distance: Double
                if (item.distance < 1.0) {
                    distance = 1.0
                    lessThan = "< "
                } else {
                    distance = item.distance
                    lessThan = ""
                }
                val nf = DecimalFormat("##.##")
                binding.txtDistance.text = "$lessThan${nf.format(distance)} Km"
            }

            binding.txtTitle.text = item.title
            binding.txtVehicleType.text = item.vehicleTypeName
            binding.txtRating.text = "(${item.totalReview})"
            binding.ratingBar.rating = item.rating.toFloat()
            binding.containerWIthDriver.isVisible = item.withDriver == 1
            binding.txtMaxPassenger.text = item.maxPassenger.toString()

            binding.containerColor.isVisible = item.colorName != null
            if (item.colorName != null) {
                binding.txtColor.text = item.colorName
                binding.colorBox.setCardBackgroundColor(Color.parseColor(item.colorValue))
            }

            if (item.withDriver == 1) {
                binding.priceWithDriverBasicContainer.isVisible = true
                binding.priceContainer.isVisible = false
                binding.txtPriceWithDriverBasic.text =
                    "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.priceWithDriverBasic)},-"

                binding.priceWithDriverFullContainer.isVisible = item.priceWithDriverFull != 0.0
                if (item.priceWithDriverFull != 0.0) {
                    binding.txtPriceWithDriverFull.text =
                        "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.priceWithDriverFull)},-"
                }
            } else {
                binding.priceWithDriverBasicContainer.isVisible = false
                binding.priceWithDriverFullContainer.isVisible = false
                binding.priceContainer.isVisible = true
                binding.txtPrice.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.price)},-"
            }

            if (item.promote == 1) {
                binding.cvHighlight.isVisible = true
                binding.itemContainer.setCardBackgroundColor(ContextCompat.getColor(ctx, R.color.promoteHighlight))
            } else {
                binding.cvHighlight.isVisible = false
                binding.itemContainer.setCardBackgroundColor(ContextCompat.getColor(ctx, R.color.baseBackgroundColor))
            }

            binding.itemContainer.setOnClickListener { onItemClick(item) }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Vehicle>() {
            override fun areItemsTheSame(oldItem: Vehicle, newItem: Vehicle) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Vehicle, newItem: Vehicle) = oldItem == newItem
        }
    }
}
