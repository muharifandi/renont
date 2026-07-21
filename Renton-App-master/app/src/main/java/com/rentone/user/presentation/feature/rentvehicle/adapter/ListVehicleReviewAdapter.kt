package com.rentone.user.presentation.feature.rentvehicle.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ItemListReviewBinding
import com.rentone.user.domain.model.Review

class ListVehicleReviewAdapter : LoadingFooterListAdapter<Review>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        ReviewViewHolder(ItemListReviewBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Review, position: Int) {
        (holder as ReviewViewHolder).bind(item)
    }

    inner class ReviewViewHolder(private val binding: ItemListReviewBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Review) {
            if (item.imgProfile != null) {
                binding.profileImage.load(Config.BASE_CUSTOMER_IMAGE + "thumb_" + item.imgProfile) {
                    error(R.drawable.user_image)
                }
            } else {
                binding.profileImage.setImageResource(R.drawable.user_image)
            }

            binding.txtName.text = item.name
            binding.txtComment.text = item.comment
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.dateModified.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.ratingBar.rating = item.rating.toFloat()
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Review>() {
            override fun areItemsTheSame(oldItem: Review, newItem: Review) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Review, newItem: Review) = oldItem == newItem
        }
    }
}
